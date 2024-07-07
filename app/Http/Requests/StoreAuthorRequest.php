<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
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
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'slug' => 'string|required|unique:authors,slug',
            'biography' => 'string|required',
            'profile_image' => 'required|image|max:1024',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => "This Author already exists in our database"
        ];
    }
}
