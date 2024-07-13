<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Author",
 *     title="Author",
 *     description="Author model",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="slug", type="string", example="john-doe"),
 *     @OA\Property(property="biography", type="string", example="Author biography text"),
 *     @OA\Property(property="profile_image", type="string", example="http://example.com/profile.jpg"),
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
 *     ),
 *     @OA\Property(
 *         property="books",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Book")
 *     )
 * )
 * @OA\Schema(
 *     schema="AuthorResource",
 *     title="AuthorResource",
 *     description="Author resource representation",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="avatar", type="string", example="http://example.com/avatar.jpg"),
 * )
 */
class Author extends Model
{
    use Uuids, HasFactory;
    
    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'biography',
        'profile_image',
      ];

      /**
       * Get all of the books for the Author
       *
       * @return \Illuminate\Database\Eloquent\Relations\HasMany
       */
      public function books(): HasMany
      {
          return $this->hasMany(Book::class);
      }
    
      public function getFullName()
      {
        return "{$this->first_name} {$this->last_name}";
      }
}
