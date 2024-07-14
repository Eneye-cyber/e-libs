<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\AuthorResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'published_at' => $this->published_at,
            'cover_image' => $this->cover_image,
            'book_file' => $this->book_file,
            'status' => $this->status,
            'author' =>  new AuthorResource($this->author),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
