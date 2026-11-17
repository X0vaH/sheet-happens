<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterClass;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\Subclass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterClass>
 */
class CharacterClassFactory extends Factory
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
            'class_id' => ClassDefinition::factory(),
            'subclass_id' => fake()->boolean() ? Subclass::factory() : null,
            'first_taken_at_character_level' => fake()->numberBetween(1, 20),
        ];
    }
}
