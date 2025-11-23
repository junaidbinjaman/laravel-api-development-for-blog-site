<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => User::query()->inRandomOrder()->first()->name ?? User::factory()->create()->name,
            'description' => fake()->sentence(),
            'post_id' => Post::query()->inRandomOrder()->first()->id ?? Post::factory()->create()->id,
            'author_id' => User::query()->inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'status' => fake()->randomElement(['draft', 'approved', 'archived'])
        ];
    }
}
