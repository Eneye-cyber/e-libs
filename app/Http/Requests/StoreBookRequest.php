<?php

namespace App\Http\Requests;

use App\Enums\BookStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreBookRequest",
 *     title="StoreBookRequest",
 *     required={"title", "cover_image", "author_id", "status", "description", "published_at"},
 *     @OA\Property(property="title", type="string", example="Sample Book"),
 *     @OA\Property(property="description", type="string", example="Sample book description."),
 *     @OA\Property(property="published_at", type="string", format="date", example="2024-07-14"),
 *     @OA\Property(property="cover_image", type="string", example="http://example.com/cover.jpg"),
 *     @OA\Property(property="book_file", type="string", example="http://example.com/book.pdf"),
 *     @OA\Property(property="author_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="status", type="string", enum={"Available", "Unavailable"}, example="Available"),
 * )
 */

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:books,title',
            'description' => 'required|string',
            'published_at'=> 'required|date|date_format:Y-m-d',
            'cover_image' => 'required|image|max:1024',
            'book_file' => 'nullable|file|mimes:pdf,doc,docx,epub|max:2048',
            'status' => [new Enum(BookStatusEnum::class), 'nullable'],
            'author_id' => 'nullable|exists:authors,id',
        ];
    }
}
