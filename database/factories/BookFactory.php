<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'title' => $this->faker->sentence(3),
            'authors' => [$this->faker->name, $this->faker->name],
            'isbn' => [
                ['type' => 'ISBN_13', 'identifier' => $this->faker->isbn13()],
                ['type' => 'ISBN_10', 'identifier' => $this->faker->isbn10()],
            ],
            'cover_url' => $this->faker->imageUrl(400, 600, 'books', true),
        ];
    }
}
