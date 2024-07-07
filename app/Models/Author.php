<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
