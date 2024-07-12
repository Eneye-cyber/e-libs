<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;
    

    /** @test */
    public function it_returns_401_for_users_not_logged_in()
    {

        $unauthenticatedResponse = [ 'message' => "Unauthenticated." ];
        $getResponse = $this->getJson('/api/books');
        $postResponse = $this->postJson('/api/books', []);
        $putResponse = $this->putJson('/api/books/someRandomeID', []);
        // $deleteResponse = $this->getJson(route('books.index'));

        $getResponse->assertStatus(401)
                 ->assertJson($unauthenticatedResponse);

        $postResponse->assertStatus(401)
                 ->assertJson($unauthenticatedResponse);

        $putResponse->assertStatus(401)
                 ->assertJson($unauthenticatedResponse);
    }

    /** @test */
    public function it_can_list_books_for_auth_users()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);
        Book::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                            ->getJson(route('books.index'));
        auth()->logout();
        

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data')
                 ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'cover_image', 
                            'author' => [
                                'id', 'name', 'avatar'
                            ], 
                            'status'
                        ],
                    ],
                 ]);
    }

    /** @test */
    public function it_can_store_a_new_book()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);
        Storage::fake('covers');
        Storage::fake('books');

        $author = Author::factory()->create();
        $cover = UploadedFile::fake()->image('cover.jpg');
        $bookFile = UploadedFile::fake()->create('book.pdf', 1000);

        $data = [
            'title' => 'New Book',
            'cover_image' => $cover,
            'author_id' => $author->id,
            'book_file' => $bookFile,
            'status' => 'Incomplete',
            'description' => fake()->paragraph(),
            'published_at' => fake()->date(),
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
                            ->postJson(route('books.store'), $data);
        auth()->logout();
        $response->assertStatus(200)
                 ->assertJson(['data' => ['title' => 'New Book']]);
        
        Storage::disk('public')->assertExists('covers/' . 'new-book.jpg');
        Storage::disk('public')->assertExists('books/' . 'new-book.pdf');
    }

    /** @test */
    public function it_can_show_a_book()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);
        $book = Book::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
                            ->getJson("/api/books/$book->id");

        $response->assertStatus(200)
                 ->assertJson(['data' => ['id' => $book->id]]);
        auth()->logout();
        
    }

    /** @test */
    public function it_can_update_a_book()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);

        $book = Book::factory()->create();
        $data = [
            "title" => "Updated Title",
        ];

        
        $response = $this->withHeader('Authorization', "Bearer $token")
                            ->putJson(route('books.update', $book->id), $data);

        $response->assertStatus(200)
                 ->assertJson(['data' => ['title' => 'Updated Title']]);
        
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function it_can_delete_a_book()
    {
        

        $author = Author::factory()->create();
        $cover = Storage::disk('public')->url('covers/book-to-be-deleted.jpg');
        $bookFile = Storage::disk('public')->url('covers/book-to-be-deleted.pdf');

        $book = Book::factory()->create([
            'title' => 'Book To Be Deleted',
            'cover_image' => $cover,
            'author_id' => $author->id,
            'book_file' => $bookFile,
            'status' => 'Incomplete',
            'description' => fake()->paragraph(),
            'published_at' => fake()->date(),
        ]);


        // Login User
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                            ->deleteJson("/api/books/$book->id");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Book deleted successfully.']);
        
        Storage::disk('public')->assertMissing('covers/' . 'book-to-be-deleted.jpg');
        Storage::disk('public')->assertMissing('books/' . 'book-to-be-deleted.pdf');
        
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
