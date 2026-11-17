<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterFeature;
use App\Models\ruleset\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterFeature>
 */
class CharacterFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => Character::factory(),
            'feature_id' => Feature::factory(),
            'acquired_at_character_level' => fake()->numberBetween(1, 20),
        ];
    }
}
