<?php

namespace App\Models;

use App\Traits\Uuids;
use App\Enums\BookStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
