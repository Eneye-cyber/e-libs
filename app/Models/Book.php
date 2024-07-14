<?php

namespace App\Models;

use App\Traits\Uuids;
use App\Enums\BookStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="Book",
 *     title="Book",
 *     description="Book model",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
 *     @OA\Property(property="title", type="string", example="Example Book Title"),
 *     @OA\Property(property="description", type="string", example="Description of the book"),
 *     @OA\Property(property="published_at", type="string", format="date", example="2024-07-13"),
 *     @OA\Property(property="cover_image", type="string", example="http://example.com/cover.jpg"),
 *     @OA\Property(property="book_file", type="string", example="http://example.com/book.pdf"),
 *     @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}, example="published"),
 *     @OA\Property(
 *         property="author",
 *         type="object",
 *         ref="#/components/schemas/Author"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-07-13T00:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-07-13T12:00:00Z"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="BookResource",
 *     title="BookResource",
 *     description="Book resource representation",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
 *     @OA\Property(property="title", type="string", example="Example Book Title"),
 *     @OA\Property(property="cover_image", type="string", example="http://example.com/cover.jpg"),
 *     @OA\Property(
 *         property="author",
 *         type="object",
 *         ref="#/components/schemas/AuthorResource"
 *     ),
 *     @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}, example="published")
 * )
 * 
 * /**
 * @OA\Schema(
 *     schema="ShowBookResource",
 *     type="object",
 *     title="Show Book Resource",
 *     description="Resource representation of a book",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="ID of the book",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title of the book",
 *         example="Example Book Title"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the book",
 *         example="This is an example description of the book."
 *     ),
 *     @OA\Property(
 *         property="published_at",
 *         type="string",
 *         format="date-time",
 *         description="Publication date of the book",
 *         example="2024-07-13T19:05:39.000000Z"
 *     ),
 *     @OA\Property(
 *         property="cover_image",
 *         type="string",
 *         description="URL of the book's cover image",
 *         example="http://example.com/cover.jpg"
 *     ),
 *     @OA\Property(
 *         property="book_file",
 *         type="string",
 *         description="URL of the book file",
 *         example="http://example.com/book.pdf"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Completion status of the book",
 *         example="incomplete"
 *     ),
 *     @OA\Property(
 *         property="author",
 *         ref="#/components/schemas/AuthorResource",
 *         description="Author of the book"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the book was created",
 *         example="2024-07-13T19:05:39.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the book was last updated",
 *         example="2024-07-13T19:05:39.000000Z"
 *     )
 * )
 *
 */

class Book extends Model
{
    use Uuids, HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'published_at',
        'cover_image',
        'book_file',
        'status',
        'author_id',
      ];

      protected $casts = [
        'status' => BookStatusEnum::class
      ];

      /**
       * Get the author of the book
       *
       * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
       */
      public function author(): BelongsTo
      {
          return $this->belongsTo(Author::class)->withDefault(function (Author $author, Book $book) {
            $author->first_name = 'Annonymous';
            $author->last_name = 'Author';
        });
      }
}
