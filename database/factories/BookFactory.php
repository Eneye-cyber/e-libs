<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Author;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => fake()->unique()->sentence(4),
            'description' => fake()->paragraph(),
            'published_at' => fake()->date(),
            'cover_image' => fake()->imageUrl(),
            'author_id' => Author::inRandomOrder()->first()->id ?? null,
        ];
    }
}
