<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassProficiency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassProficiency>
 */
class ClassProficiencyFactory extends Factory
{
    protected $model = ClassProficiency::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => CharacterClass::factory(),
            'type' => fake()->randomElement(['armor', 'weapon']),
            'label' => fake()->words(3, true),
        ];
    }
}
