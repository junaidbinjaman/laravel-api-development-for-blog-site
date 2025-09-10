<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()->count(6)->create();
        $categories = Category::factory()->count(6)->create();
        $thumbnail = [
            'https://images.pexels.com/photos/3560020/pexels-photo-3560020.jpeg',
            'https://images.pexels.com/photos/122107/pexels-photo-122107.jpeg',
            'https://images.pexels.com/photos/34413757/pexels-photo-34413757.jpeg'
        ];

        for ($i = 0; $i <= 20; $i++) {
            Post::factory()->create([
                'author_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'thumbnail' => $thumbnail[rand(0, 2)]
            ]);
        }
    }
}
