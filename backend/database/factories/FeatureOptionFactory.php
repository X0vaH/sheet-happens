<?php

namespace Database\Factories;

use App\Models\FeatureOption;
use App\Models\ruleset\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeatureOption>
 */
class FeatureOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feature_id' => Feature::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
