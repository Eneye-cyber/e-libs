<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
