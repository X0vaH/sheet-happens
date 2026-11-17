<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\StartingEquipmentGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StartingEquipmentGroup>
 */
class StartingEquipmentGroupFactory extends Factory
{
    protected $model = StartingEquipmentGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => CharacterClass::factory(),
            'label' => fake()->words(2, true),
        ];
    }
}
