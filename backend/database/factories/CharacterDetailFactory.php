<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\CharacterDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterDetail>
 */
class CharacterDetailFactory extends Factory
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
            'ideals' => fake()->sentence(),
            'bonds' => fake()->sentence(),
            'flaws' => fake()->sentence(),
            'backstory' => fake()->paragraphs(3, true),
            'personality_traits' => fake()->sentence(),
        ];
    }
}
