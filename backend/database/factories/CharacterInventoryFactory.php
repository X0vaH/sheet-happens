<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterInventory;
use App\Models\ruleset\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterInventory>
 */
class CharacterInventoryFactory extends Factory
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
            'item_id' => Item::factory(),
            'quantity' => fake()->numberBetween(1, 20),
        ];
    }
}
