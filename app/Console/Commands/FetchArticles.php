<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use DB;
use Illuminate\Support\Facades\Redis;


class FetchArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-articles';
    // Command signature and description

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Fetch articles from various news sources and store them in the database';

    public function __construct()
    {
        parent::__construct();

    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Article::truncate();
            $this->getNewsAPIData();
            $this->getGuardianNewsData();
            $this->getNYTimesData();
            Redis::flushAll();


        } catch (\Exception $e) {
            // Roll back the transaction if there's an error
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }

    public function getNewsAPIData()
    {
        try {
            $categories = config("constants.categories");

            // Example of fetching articles from a source like NewsAPI
            foreach ($categories as $category) {
                $response = Http::withOptions(['verify' => false])->get('https://newsapi.org/v2/top-headlines', [
                    'apiKey' => env('NEWSAPI_API_KEY'),
                    'category' => $category,
                    'country' => 'us',
                ]);

                if ($response->successful()) {
                    $articles = $response->json()['articles'];
                    foreach ($articles as $articleData) {
                        Article::updateOrCreate(
                            ['url' => $articleData['url']],  // Unique identifier
                            [
                                'description' => $articleData['description'],

                                'title' => $articleData['title'],
                                'published_at' => Carbon::parse($articleData['publishedAt'])->format('Y-m-d'), // Convert to MySQL datetime,
                                'source' => "News API",
                                "content" => $articleData['content'] ? $articleData['content'] : "",
                                "author" => $articleData['author'] ? $articleData['author'] : "",
                                "category" => $category
                            ]
                        );
                    }


                } else {
                    $this->error('Failed to fetch articles');
                }
            }
            $this->info('Articles have been successfully fetched and stored from News API source.');

        } catch (\Exception $e) {
            // Roll back the transaction if there's an error
            $this->error('An error occurred: ' . $e->getMessage());
        }

    }

    public function getGuardianNewsData()
    {

        try {
            $categories = config("constants.categories");

            // Example of fetching articles from a source like NewsAPI
            foreach ($categories as $category) {
                $response = Http::withOptions(['verify' => false])->get('https://content.guardianapis.com/search', [
                    'api-key' => env('GUARDIAN_API_KEY'),
                    'q' => $category,
                ]);

                if ($response->successful()) {
                    $articles = $response->json()['response'];
                    $articles = isset($articles['results']) ? $articles['results'] : [];

                    foreach ($articles as $articleData) {
                        Article::updateOrCreate(
                            ['title' => $articleData['webTitle']],  // Unique identifier
                            [
                                'url' => $articleData['id'],
                                'published_at' => Carbon::parse($articleData['webPublicationDate'])->format('Y-m-d'), // Convert to MySQL datetime,
                                'source' => "The Guardian",
                                "content" => isset($articleData['content']) ? $articleData['content'] : "",
                                "author" => isset($articleData['author']) ? $articleData['author'] : "",
                                "category" => $category
                            ]
                        );
                    }


                } else {
                    $this->error('Failed to fetch articles');
                }
            }
            $this->info('Articles have been successfully fetched and stored from The Guardian Source.');

        } catch (\Exception $e) {
            // Roll back the transaction if there's an error
            $this->error('An error occurred: ' . $e->getMessage());
        }

    }

    public function getNYTimesData()
    {
        try {
            $categories = config("constants.categories");

            // Example of fetching articles from a source like NewsAPI
            foreach ($categories as $category) {
                $response = Http::withOptions(['verify' => false])->get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
                    'api-key' => env('NY_TIMES_API_KEY'),
                    'q' => $category,
                ]);
                if ($response->successful()) {
                    $articles = $response->json()['response'];
                    $articles = isset($articles['docs']) ? $articles['docs'] : [];
                    foreach ($articles as $articleData) {
                        Article::updateOrCreate(
                            ['title' => $articleData['abstract']],
                            [
                                'description' => isset($articleData['lead_paragraph']) ? $articleData['lead_paragraph'] : "",
                                'url' => isset($articleData['web_url']) ? $articleData['web_url'] : "",
                                'published_at' => Carbon::parse($articleData['pub_date'])->format('Y-m-d'), // Convert to MySQL datetime,
                                'source' => "New York Times",
                                "content" => isset($articleData['snippet']) ? $articleData['snippet'] : "",
                                "author" => isset($articleData['author']) ? $articleData['author'] : "",
                                "category" => $category
                            ]
                        );
                    }


                } else {
                    $this->error('Failed to fetch articles');
                }
            }
            $this->info('Articles have been successfully fetched and stored from New York Times source.');

        } catch (\Exception $e) {
            // Roll back the transaction if there's an error
            $this->error('An error occurred: ' . $e->getMessage());
        }

    }




}
