<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Level;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubclassLevel>
 */
class SubclassLevelFactory extends Factory
{
    protected $model = SubclassLevel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subclass_id' => Subclass::query()->inRandomOrder()->value('id') ?? Subclass::factory()->create()->id,
            'level_id' => Level::query()->inRandomOrder()->value('id') ?? Level::factory()->create()->id,
        ];
    }
}
