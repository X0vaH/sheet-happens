<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterAbility;
use App\Models\ruleset\Ability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterAbility>
 */
class CharacterAbilityFactory extends Factory
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
            'score' => fake()->numberBetween(3, 20),
        ];
    }
}
