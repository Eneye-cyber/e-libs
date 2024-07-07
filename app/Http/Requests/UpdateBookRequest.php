<?php

namespace App\Http\Requests;

use App\Enums\BookStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

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
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255|unique:books,title,',
            'description' => 'sometimes|string',
            'published_at' => 'sometimes|date|date_format:Y-m-d',
            'cover_image' => 'sometimes|url',
            'book_file' => 'sometimes|nullable|url',
            'status' => [new Enum(BookStatusEnum::class), 'sometimes', 'nullable'],
            'author_id' => 'sometimes|nullable|exists:authors,id',
        ];
    }
}
