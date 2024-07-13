<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UploadFileRequest",
 *     title="UploadFileRequest",
 *     required={"id", "group"},
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="group", type="string", enum={"author", "book"}, example="book"),
 *     @OA\Property(property="image", type="string", format="binary", example="image data"),
 *     @OA\Property(property="book", type="string", format="binary", example="book file data"),
 * )
 */
class UploadFileRequest extends FormRequest
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
            'id' => 'bail|required|uuid',
            'group' => 'bail|required|in:author,book',
            'image' => 'bail|image|max:1024|required_if:book,',
            'book' => 'bail|file|mimes:pdf,doc,docx,epub|max:2048|required_if:image,',
        ];
    }
}
