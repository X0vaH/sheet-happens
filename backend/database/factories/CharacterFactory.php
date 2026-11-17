<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\ruleset\Ruleset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxHealthPoints = fake()->numberBetween(10, 200);

        return [
            'user_id' => User::factory(),
            'ruleset_id' => Ruleset::factory(),
            'name' => fake()->firstName(),
            'current_health_points' => fake()->numberBetween(1, $maxHealthPoints),
            'max_health_points' => $maxHealthPoints,
            'alignment' => fake()->randomElement([
                'Lawful Good', 'Neutral Good', 'Chaotic Good',
                'Lawful Neutral', 'True Neutral', 'Chaotic Neutral',
                'Lawful Evil', 'Neutral Evil', 'Chaotic Evil',
            ]),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
