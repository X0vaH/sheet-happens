<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterLevel>
 */
class CharacterLevelFactory extends Factory
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
            'character_level' => fake()->numberBetween(1, 20),
            'experience_points' => fake()->numberBetween(0, 355000),
        ];
    }
}
