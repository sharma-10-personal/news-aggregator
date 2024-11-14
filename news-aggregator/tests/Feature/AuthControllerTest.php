<?php

namespace Tests\Feature;

use App\Models\UserData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration.
     *
     * @return void
     */
    public function test_register_user()
    {
        // Data for registration
        $data = [
            'email_id' => 'testuser@example.com',
            'password' => 'password123',
        ];

        // Send POST request to register
        $response = $this->json('POST', '/api/register', $data);

        // Assert that the response contains a token
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    /**
     * Test user login with valid credentials.
     *
     * @return void
     */
    public function test_login_user()
    {
        // Create a user in the database
        $user = UserData::factory()->create([
            'email_id' => 'testuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Data for login
        $data = [
            'email_id' => 'testuser@example.com',
            'password' => 'password123',
        ];

        // Send POST request to login
        $response = $this->json('POST', '/api/login', $data);

        // Assert that the response contains a token
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    /**
     * Test login with invalid credentials.
     *
     * @return void
     */
    public function test_login_user_with_invalid_credentials()
    {
        // Data for login with incorrect credentials
        $data = [
            'email_id' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ];

        // Send POST request to login
        $response = $this->json('POST', '/api/login', $data);

        // Assert that the response status is 401 (Unauthorized)
        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid credentials']);
    }

    /**
     * Test user logout.
     *
     * @return void
     */
    public function test_logout_user()
    {
        // Create a user and authenticate
        $user = UserData::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Send POST request to logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', '/api/logout');

        // Assert that the user is logged out
        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }


}
