<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterSpellSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterSpellSlot>
 */
class CharacterSpellSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxSlots = fake()->numberBetween(1, 4);

        return [
            'character_id' => Character::factory(),
            'spell_level' => fake()->numberBetween(1, 9),
            'max_slots' => $maxSlots,
            'expended_slots' => fake()->numberBetween(0, $maxSlots),
        ];
    }
}
