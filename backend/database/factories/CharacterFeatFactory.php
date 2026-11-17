<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterFeat;
use App\Models\ruleset\Feat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterFeat>
 */
class CharacterFeatFactory extends Factory
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
            'feat_id' => Feat::factory(),
            'acquired_at_character_level' => fake()->numberBetween(4, 20),
        ];
    }
}
