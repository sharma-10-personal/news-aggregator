<?php

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="News Aggregator API",
 *      description="API documentation for News Aggregator project",
 *      @OA\Contact(
 *          email="support@example.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://127.0.0.1:8000",
 *      description="Local Development Server"
 * )
 */


use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user/articles', [ArticleController::class, 'getUserArticles']);
Route::middleware('auth:sanctum')->get('/user/get-article-by-id/{id}', [ArticleController::class, 'getUserArticleById']);


Route::middleware('auth:sanctum')->group(function () {
    // Get user preferences
    Route::get('/preferences', [PreferenceController::class, 'getPreferences']);

    // Set user preferences
    Route::post('/preferences', [PreferenceController::class, 'setPreferences']);

    // Get personalized news feed
    Route::get('/personalized-news', [PreferenceController::class, 'getPersonalizedNews']);
});


Route::get('/login', function () {
    return response()->json(['message' => 'Please log in. The session is expired'], 401);
})->name('login');