<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterSkillProficiency;
use App\Models\ruleset\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterSkillProficiency>
 */
class CharacterSkillProficiencyFactory extends Factory
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
            'skill_id' => Skill::factory(),
            'proficiency_level' => fake()->randomElement(['none', 'proficient', 'expertise']),
        ];
    }
}
