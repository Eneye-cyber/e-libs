<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Author>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $name = "{$firstName} {$lastName}";

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'slug' => Str::slug($name),
            'biography' => fake()->paragraph(10),
            'profile_image' => fake()->imageUrl(), // password
        ];
    }
}
