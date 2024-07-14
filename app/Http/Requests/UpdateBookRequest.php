<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use App\Enums\BookStatusEnum;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateBookRequest",
 *     title="UpdateBookRequest",
 *     @OA\Property(property="title", type="string", example="Updated Book Title"),
 *     @OA\Property(property="description", type="string", example="Updated book description."),
 *     @OA\Property(property="published_at", type="string", format="date", example="2024-07-14"),
 *     @OA\Property(property="cover_image", type="string", example="http://example.com/new_cover.jpg"),
 *     @OA\Property(property="book_file", type="string", example="http://example.com/new_book.pdf"),
 *     @OA\Property(property="author_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="status", type="string", enum={"Available", "Unavailable"}, example="Unavailable"),
 * )
 */
class UpdateBookRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        $bookId = $request->route('book');
        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('books', 'title')->ignore($bookId),
            ],
            'description' => 'sometimes|string',
            'published_at' => 'sometimes|date|date_format:Y-m-d',
            'cover_image' => 'sometimes|url',
            'book_file' => 'sometimes|nullable|url',
            'status' => [new Enum(BookStatusEnum::class), 'sometimes', 'nullable'],
            'author_id' => 'sometimes|nullable|exists:authors,id',
        ];
    }
}
