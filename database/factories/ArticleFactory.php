<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,  // Random sentence for the title
            'description' => $this->faker->paragraph,  // Random paragraph for description
            'content' => $this->faker->text(1000),  // Random long text for content
            'url' => $this->faker->unique()->url,  // Unique random URL
            'author' => $this->faker->name,  // Random author name
            'source' => $this->faker->company,  // Random company name for the source
            'category' => $this->faker->word,  // Random word for category
            'published_at' => $this->faker->dateTimeThisYear(),  // Random datetime within the current year
        ];
    }
}
