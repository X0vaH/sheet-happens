<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Alignment;
use App\Models\ruleset\Ruleset;
use Database\Seeders\ruleset\AlignmentSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alignment>
 */
class AlignmentFactory extends Factory
{
    protected $model = Alignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(array_keys(AlignmentSeeder::DEFINITIONS));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'description' => AlignmentSeeder::DEFINITIONS[$name],
        ];
    }
}
