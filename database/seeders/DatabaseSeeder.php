<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            CategorySeeder::class,
            PostSeeder::class,
            CommentSeeder::class
        ]);

        User::factory()->createMany([
            [
                'email' => 'ersome65859@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'email' => 'junaid@allnextver.com',
                'password' => Hash::make('password'),
                'role' => 'author'
            ],
            [
                'email' => 'ursomed@allnextver.com',
                'password' => Hash::make('password'),
                'role' => 'user'
            ],
        ]);
    }
}
