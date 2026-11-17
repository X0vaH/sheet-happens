<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Level>
 */
class LevelFactory extends Factory
{
    protected $model = Level::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $levelNumber = fake()->numberBetween(1, 20);

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'level_number' => $levelNumber,
            // Progression officielle du bonus de maîtrise : +2 aux niveaux 1-4, +3 aux 5-8, etc.
            'proficiency_bonus' => 2 + intdiv($levelNumber - 1, 4),
        ];
    }
}
