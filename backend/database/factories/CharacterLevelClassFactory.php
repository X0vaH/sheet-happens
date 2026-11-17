<?php

namespace Database\Factories;

use App\Models\CharacterClass;
use App\Models\CharacterLevel;
use App\Models\CharacterLevelClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterLevelClass>
 */
class CharacterLevelClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_class_id' => CharacterClass::factory(),
            'character_level_id' => CharacterLevel::factory(),
            'resulting_class_level' => fake()->numberBetween(1, 20),
        ];
    }
}
