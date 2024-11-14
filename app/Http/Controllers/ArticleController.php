<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;


class ArticleController extends Controller
{
    public function getUserArticles(Request $request)
    {
        // Define the number of items per page, or get from the request
        $perPage = $request->input('limit', 10); // Default to 10 articles per page
        // Generate a unique cache key based on the filters
        $cacheKey = 'articles:' . md5(json_encode($request->all()));

        // Check if articles are cached in Redis
        if (Redis::exists($cacheKey)) {
            $cachedData = json_decode(Redis::get($cacheKey), true);

            // Extract articles and pagination information from the cached data
            $articlesData = $cachedData['data']; // The list of articles
            $pagination = $cachedData['pagination']; // The pagination details
        } else {
            // Start building the query for fetching articles
            $query = Article::query();

            // Apply filter for date if provided
            if ($request->has('date')) {
                $query->whereDate('published_at', $request->input('date')); // Filter by creation date
            }

            // Apply filter for category if provided 
            if ($request->has('category')) {
                $query->where('category', $request->input('category')); // Filter by category
            }

            // Apply filter for source if provided 
            if ($request->has('source')) {
                $query->where('source', $request->input('source')); // Filter by source
            }

            // Fetch filtered and paginated articles
            $articles = $query->paginate($perPage);

            // Cache the result in Redis for future requests, setting an expiration time of 60 minutes
            Redis::setex($cacheKey, 3600, json_encode([
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'last_page' => $articles->lastPage(),
                    'next_page_url' => $articles->nextPageUrl(),
                    'prev_page_url' => $articles->previousPageUrl(),
                ]
            ]));

            // Cache the result in Redis for future requests, setting an expiration time of 60 minutes
            Redis::setex($cacheKey, 3600, json_encode([
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'last_page' => $articles->lastPage(),
                    'next_page_url' => $articles->nextPageUrl(),
                    'prev_page_url' => $articles->previousPageUrl(),
                ]
            ]));

            // Set data for response
            $articlesData = $articles->items();
            $pagination = [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
                'next_page_url' => $articles->nextPageUrl(),
                'prev_page_url' => $articles->previousPageUrl(),
            ];

        }

        // Return response with articles data and pagination
        return response()->json([
            'status' => 'success',
            'data' => $articlesData,
            'pagination' => $pagination,
        ]);
    }

    public function getUserArticleById($id)
    {
        // Define the cache key based on the article ID
        $cacheKey = 'article:' . $id;

        // Check if the article is cached in Redis
        if (Redis::exists($cacheKey)) {
            // Retrieve the cached article data from Redis and decode it
            $cachedArticle = json_decode(Redis::get($cacheKey), true);

            // Return the cached article
            return response()->json([
                'status' => 'success',
                'data' => $cachedArticle,
            ]);
        }

        // Article is not found in the cache, fetch it from the database
        $article = Article::find($id);

        // Check if the article exists in the database
        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article not found',
            ], 404);
        }

        // Store the article in Redis with an expiration time of 60 minutes (3600 seconds)
        Redis::setex($cacheKey, 3600, json_encode($article));

        // Return the article data
        return response()->json([
            'status' => 'success',
            'data' => $article,
        ]);
    }
}