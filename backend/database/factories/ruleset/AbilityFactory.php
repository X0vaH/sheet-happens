<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ability>
 */
class AbilityFactory extends Factory
{
    protected $model = Ability::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $abilities = [
            'Force' => 'FOR',
            'Dextérité' => 'DEX',
            'Constitution' => 'CON',
            'Intelligence' => 'INT',
            'Sagesse' => 'SAG',
            'Charisme' => 'CHA',
        ];

        $name = fake()->randomElement(array_keys($abilities));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'abbreviation' => $abilities[$name],
        ];
    }
}
