<?php

namespace Tests\Unit\Http\Controllers;

use Tests\TestCase;
use App\Models\UserData;
use App\Models\Article;
use App\Models\Preference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Set up any common test state here
    }

    /** @test */
    public function it_can_set_preferences_and_clear_redis_cache()
    {
        // Mock the user and preferences data
        $user = UserData::factory()->create();
        $this->actingAs($user);

        // Mock request data
        $requestData = [
            'preferred_sources' => ['source1', 'source2'],
            'preferred_categories' => ['category1'],
            'preferred_authors' => ['author1']
        ];

        // Set up Redis to expect deletion of cache key
        Redis::shouldReceive('del')->once()->with('user:preferences:' . $user->id);

        // Call the controller method
        $response = $this->postJson('/api/preferences', $requestData);

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Preferences updated successfully!',
            ]);

        // Fetch the preferences from the database
        $preferences = DB::table('preferences')->where('user_id', $user->id)->first();

        $this->assertNotNull($preferences, 'Preferences were not found in the database.');

        // Decode JSON fields for comparison
        $this->assertEquals($requestData['preferred_sources'], json_decode($preferences->preferred_sources, true));
        $this->assertEquals($requestData['preferred_categories'], json_decode($preferences->preferred_categories, true));
        $this->assertEquals($requestData['preferred_authors'], json_decode($preferences->preferred_authors, true));
    }


    /** @test */
    public function it_can_get_preferences_from_redis_if_available()
    {
        // Mock the user and preferences data
        $user = UserData::factory()->create();
        $this->actingAs($user);
        $cacheKey = 'user:preferences:' . $user->id;

        // Simulate cached preferences data in Redis
        $preferences = [
            'preferred_sources' => ['source1'],
            'preferred_categories' => ['category1'],
            'preferred_authors' => ['author1'],
        ];

        // Insert preferences in the database with encoded JSON for consistency
        DB::table('preferences')->insert([
            'user_id' => $user->id,
            'preferred_sources' => json_encode(['source1', 'source2']),
            'preferred_categories' => json_encode(['category1']),
            'preferred_authors' => json_encode(['author1']),
        ]);

        // Set up Redis to return the cached preferences
        Redis::shouldReceive('get')->once()->with($cacheKey)->andReturn(json_encode($preferences));

        // Call the controller method
        $response = $this->getJson('/api/preferences');

        // Assert response status and structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'preferred_sources',
                    'preferred_categories',
                    'preferred_authors'
                ],
            ]);

        // Fetch the response data and check for null to avoid error
        $responseData = $response->json('data');
        $this->assertNotNull($responseData, 'Response data is null.');

        // Assert that the returned data matches the expected preferences
        $this->assertEquals($preferences['preferred_sources'], $responseData['preferred_sources']);
        $this->assertEquals($preferences['preferred_categories'], $responseData['preferred_categories']);
        $this->assertEquals($preferences['preferred_authors'], $responseData['preferred_authors']);
    }

    /** @test */
    public function test_get_personalized_news_from_cache()
    {
        $user = UserData::factory()->create();  // Create a user
        $this->actingAs($user);

        // Mock the Redis cache with some dummy data
        $cacheKey = 'personalized_news:' . $user->id . ':' . md5(json_encode($user->preferences)) . ':10:1';
        $cachedData = [
            'data' => [['title' => 'Cached News Item']],
            'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 10, 'total' => 1],
        ];

        Redis::shouldReceive('exists')->with($cacheKey)->once()->andReturn(true);
        Redis::shouldReceive('get')->with($cacheKey)->once()->andReturn(json_encode($cachedData));

        $response = $this->getJson('/api/personalized-news');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => $cachedData['data'],
                'pagination' => $cachedData['pagination'],
            ]);
    }

    /** @test */
    public function test_get_personalized_news_no_preferences()
    {
        $user = UserData::factory()->create();  // Create a user with no preferences
        $this->actingAs($user);

        $response = $this->getJson('/api/personalized-news');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [],
                'message' => 'No preferences set yet',
            ]);
    }
}
