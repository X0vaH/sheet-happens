<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterAbilityIncrease;
use App\Models\ruleset\Ability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterAbilityIncrease>
 */
class CharacterAbilityIncreaseFactory extends Factory
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
            'ability_id' => Ability::factory(),
            'increase_amount' => fake()->numberBetween(1, 2),
            'acquired_at_character_level' => fake()->numberBetween(4, 20),
        ];
    }
}
