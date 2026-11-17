<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterOrigin;
use App\Models\ruleset\Background;
use App\Models\ruleset\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterOrigin>
 */
class CharacterOriginFactory extends Factory
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
            'specie_id' => Species::factory(),
            'background_id' => Background::factory(),
        ];
    }
}
