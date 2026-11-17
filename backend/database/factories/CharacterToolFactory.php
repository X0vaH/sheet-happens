<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterTool;
use App\Models\ruleset\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterTool>
 */
class CharacterToolFactory extends Factory
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
            'tool_id' => Tool::factory(),
            'proficiency_level' => fake()->randomElement(['none', 'proficient', 'expertise']),
        ];
    }
}
