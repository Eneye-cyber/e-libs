<?php

namespace App\Http\Requests;

use App\Enums\BookStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

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
