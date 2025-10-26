<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(rand(2, 5), true);
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'content' => fake()->paragraphs(rand(3, 7), true),
            'excerpt' => fake()->paragraph(),
            'status' => 'published',
        ];
    }
}
