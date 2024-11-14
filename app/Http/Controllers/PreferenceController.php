<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Redis;
use Closure;
use Exception;

class PreferenceController extends Controller
{
    public function setPreferences(Request $request)
    {
        // Validate the input
        $request->validate([
            'preferred_sources' => 'nullable|array',
            'preferred_sources.*' => 'string',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => 'string',
            'preferred_authors' => 'nullable|array',
            'preferred_authors.*' => 'string',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Retrieve or create preferences for the user
        $preferences = $user->preferences()->first(); // Try to fetch existing preferences

        // If preferences do not exist, create a new one
        if (!$preferences) {
            $preferences = $user->preferences()->create([
                'preferred_sources' => $request->input('preferred_sources', []),
                'preferred_categories' => $request->input('preferred_categories', []),
                'preferred_authors' => $request->input('preferred_authors', []),
            ]);
        } else {
            // Update preferences if they exist
            $preferences->update([
                'preferred_sources' => $request->input('preferred_sources', []),
                'preferred_categories' => $request->input('preferred_categories', []),
                'preferred_authors' => $request->input('preferred_authors', []),
            ]);
        }

        //deleting the existing redis key
        $cacheKey = 'user:preferences:' . $user->id;
        Redis::del($cacheKey);

        // Return response
        return response()->json([
            'status' => 'success',
            'message' => 'Preferences updated successfully!',
        ]);
    }


    public function getPreferences(Request $request)
    {
        try {
            // Get the authenticated user
            $user = $request->user();

            // Define the Redis cache key
            $cacheKey = 'user:preferences:' . $user->id;

            // Check if preferences are available in Redis
            $cachedPreferences = Redis::get($cacheKey);

            if ($cachedPreferences) {
                // If available in Redis, decode the JSON and return it
                $preferences = json_decode($cachedPreferences, true);
            } else {
                // If not available in Redis, retrieve from the database
                $preferences = $user->preferences()->first();

                if ($preferences) {
                    // Cache the preferences in Redis
                    Redis::set($cacheKey, json_encode($preferences->toArray()));
                }
            }

            // Return preferences if found, otherwise return an empty response
            return response()->json([
                'status' => 'success',
                'data' => is_array($preferences) ? $preferences : $preferences->toArray()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);

        }
    }



    public function getPersonalizedNews(Request $request)
    {
        try {
            $user = $request->user();

            // Generate a cache key based on the user ID, preferences, and query parameters
            $cacheKey = 'personalized_news:' . $user->id . ':' . md5(json_encode($user->preferences)) . ':' . $request->input('limit', 10) . ':' . $request->input('page', 1);

            // Check if the personalized news data is already cached in Redis
            if (Redis::exists($cacheKey)) {
                // Retrieve the cached data from Redis and decode it
                $cachedData = json_decode(Redis::get($cacheKey), true);

                // Return the cached response
                return response()->json([
                    'status' => 'success',
                    'data' => $cachedData['data'],
                    'pagination' => $cachedData['pagination'],
                ]);
            }

            // Get user preferences
            $preferences = $user->preferences()->first();

            // If no preferences, return an empty response
            if (!$preferences) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'No preferences set yet',
                ]);
            }

            // Fetch personalized news based on user preferences
            $query = Article::query();

            // Apply filters based on preferences
            if ($preferences->preferred_sources) {
                $query->whereIn('source', $preferences->preferred_sources);
            }

            if ($preferences->preferred_categories) {
                $query->whereIn('category', $preferences->preferred_categories);
            }

            if ($preferences->preferred_authors) {
                $query->whereIn('author', $preferences->preferred_authors);
            }

            // Fetch and paginate the articles
            $perPage = $request->input('limit', 10);
            $articles = $query->paginate($perPage);

            // Prepare the data to be cached
            $responseData = [
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'last_page' => $articles->lastPage(),
                    'next_page_url' => $articles->nextPageUrl(),
                    'prev_page_url' => $articles->previousPageUrl(),
                ],
            ];

            // Store the personalized news data in Redis with an expiration time of 60 minutes (3600 seconds)
            Redis::setex($cacheKey, 3600, json_encode($responseData));

            // Return the personalized news data
            return response()->json([
                'status' => 'success',
                'data' => $responseData['data'],
                'pagination' => $responseData['pagination'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
