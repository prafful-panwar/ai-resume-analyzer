<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobDescription>
 */
class JobDescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_role' => $this->faker->jobTitle(),
            'experience_min' => $this->faker->numberBetween(1, 4),
            'experience_max' => $this->faker->numberBetween(5, 10),
            'description' => $this->faker->paragraphs(3, true),
            'requirements' => [
                $this->faker->word(),
                $this->faker->word(),
                $this->faker->word(),
            ],
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
