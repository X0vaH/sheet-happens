<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterSpell;
use App\Models\ruleset\Spell;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterSpell>
 */
class CharacterSpellFactory extends Factory
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
            'spell_id' => Spell::factory(),
            'acquired_at_character_level' => fake()->numberBetween(1, 20),
        ];
    }
}
