<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassLevel>
 */
class ClassLevelFactory extends Factory
{
    protected $model = ClassLevel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => CharacterClass::query()->inRandomOrder()->value('id') ?? CharacterClass::factory()->create()->id,
            'level_id' => Level::query()->inRandomOrder()->value('id') ?? Level::factory()->create()->id,
        ];
    }
}
