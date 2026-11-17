<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterLanguage;
use App\Models\ruleset\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterLanguage>
 */
class CharacterLanguageFactory extends Factory
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
            'language_id' => Language::factory(),
            'source' => fake()->randomElement(['species', 'background', 'class', 'feat']),
        ];
    }
}
