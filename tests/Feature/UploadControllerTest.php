<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Book;
use ReflectionClass;
use App\Models\Author;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UploadFileRequest;
use App\Http\Controllers\UploadController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('avatars');
        Storage::fake('covers');
        Storage::fake('books');
    }

    /** @test */
    public function test_update_file_with_author_group()
    {
        Log::shouldReceive('info');
        
        $author = Author::factory()->create();
        $image = UploadedFile::fake()->image('avatar.jpg');
        
        $request = UploadFileRequest::create('/upload', 'POST', [
            'group' => 'author',
            'id' => $author->id,
            'image' => $image,
        ]);

        $controller = $this->getMockBuilder('App\Http\Controllers\UploadController')
            ->onlyMethods(['updateProfilePicture'])
            ->getMock();

        $controller->expects($this->once())
            ->method('updateProfilePicture')
            ->willReturn('profile picture updated');

        $response = $controller->updateFile($request);
        
        $this->assertEquals('profile picture updated', $response);
    }

    /** @test */
    public function test_update_file_with_book_group()
    {
        Log::shouldReceive('info');
        
        $book = Book::factory()->create();
        $image = UploadedFile::fake()->image('cover.jpg');
        
        $request = UploadFileRequest::create('/upload', 'POST', [
            'group' => 'book',
            'id' => $book->id,
            'image' => $image,
        ]);

        $controller = $this->getMockBuilder('App\Http\Controllers\UploadController')
            ->onlyMethods(['updateBookMedia'])
            ->getMock();

        $controller->expects($this->once())
            ->method('updateBookMedia')
            ->willReturn('book media updated');

        $response = $controller->updateFile($request);
        
        $this->assertEquals('book media updated', $response);
    }

    /** @test */
    public function test_update_profile_picture_success()
    {
        Log::shouldReceive('info');
        
        $author = Author::factory()->create();
        $image = UploadedFile::fake()->image('avatar.jpg');
        
        $controller = new UploadController();

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('updateProfilePicture');
        $method->setAccessible(true);
        $data = [
            'id' => $author->id,
            'image' => $image,
        ];
        $response = $method->invokeArgs($controller, [$data]);

        $this->assertEquals(200, $response->status());
        Storage::disk('public')->assertExists("avatar/$author->slug.jpg");
    }

    /** @test */
    public function test_update_book_media_success()
    {
        Log::shouldReceive('info');
        
        $book = Book::factory()->create();
        $image = UploadedFile::fake()->image('cover.jpg');
        $file = UploadedFile::fake()->create('book.pdf');

        $bookSlug = Str::slug($book->title);
        $controller = new UploadController();

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('updateBookMedia');
        $method->setAccessible(true);
        $data = [
            'id' => $book->id,
            'image' => $image,
            'book' => $file,
        ];
        $response = $method->invokeArgs($controller, [$data]);


        $this->assertEquals(200, $response->status());
        $this->assertEquals('Book Media Updated Successfully', $response->getData()->data->message);
        Storage::disk('public')->assertExists("covers/$bookSlug.jpg");
        Storage::disk('public')->assertExists("books/$bookSlug.pdf");
    }

    /** @test */
    public function test_update_profile_picture_failure()
    {
        Log::shouldReceive('info');
        Log::shouldReceive('error');
        
        $controller = new UploadController();

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('updateProfilePicture');
        $method->setAccessible(true);
        $data = [
            'id' => 999, // Non-existent ID
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $method->invokeArgs($controller, [$data]);


        $this->assertEquals(503, $response->status());
        $this->assertEquals('Server Error', $response->getData()->message);
    }

    /** @test */
    public function test_update_book_media_failure()
    {
        Log::shouldReceive('info');
        Log::shouldReceive('error');
        
        $controller = new UploadController();

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('updateBookMedia');
        $method->setAccessible(true);
        $data = [
            'id' => 999, // Non-existent ID
            'image' => UploadedFile::fake()->image('cover.jpg'),
            'book' => UploadedFile::fake()->create('book.pdf'),
        ];
        $response = $method->invokeArgs($controller, [$data]);


        $this->assertEquals(503, $response->status());
        $this->assertEquals('Server Error', $response->getData()->message);
    }
}
