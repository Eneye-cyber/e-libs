<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_books_and_authors_by_auth_users()
    {
        $author = Author::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'slug' => 'john-doe',
            'biography' => fake()->paragraph(10),
            'profile_image' => fake()->imageUrl(), // password
        ]);
        $book = Book::factory()->create([
            'title' => 'John\'s Adventures',
            'description' => fake()->paragraph(),
            'published_at' => fake()->date(),
            'cover_image' => fake()->imageUrl(),
            'author_id' => Author::inRandomOrder()->first()->id ?? null,
        ]);

        $user = User::factory()->create();

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/search?query=John');


        // $response = $this->getJson('/search?query=John');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'data' => [
                        'books' => [
                            '*' => ['id', 'title', 'cover_image', 
                            'author' => [
                               'id', 'name', 'avatar'
                           ], 'status'],
                        ],
                        'authors' => [
                            '*' => [
                                'id', 'name', 'avatar'
                            ],
                        ],
                    ],
                 ]);
    }

    public function test_search_books_and_authors_by_users_not_logged_in()
    {

        $response = $this->getJson('/api/search?query=John');

        $response->assertStatus(401)
                 ->assertJson([ 'message' => "Unauthenticated." ]);
    }
}
