<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateAuthorRequest",
 *     title="UpdateAuthorRequest",
 *     @OA\Property(property="first_name", type="string", example="Updated John"),
 *     @OA\Property(property="last_name", type="string", example="Updated Doe"),
 *     @OA\Property(property="biography", type="string", example="Updated author biography."),
 *     @OA\Property(property="profile_image", type="string", example="http://example.com/new_profile.jpg"),
 * )
 */
class UpdateAuthorRequest extends FormRequest
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
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'biography' => 'sometimes|string',
            'profile_image' => 'sometimes|url',
        ];
    }
}
