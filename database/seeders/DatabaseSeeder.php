<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Author::factory(50)->create()->each(function ($author) {
            $author->books()->saveMany(
                Book::factory(rand(2, 5))->create(['author_id' => $author->id])->each(function ($book) {
                    $book->reviews()->saveMany(Review::factory(rand(0, 10))->make());
                })
            );
        });
    }
}
