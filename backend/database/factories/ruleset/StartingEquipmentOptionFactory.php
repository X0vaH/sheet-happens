<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\StartingEquipmentGroup;
use App\Models\ruleset\StartingEquipmentOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StartingEquipmentOption>
 */
class StartingEquipmentOptionFactory extends Factory
{
    protected $model = StartingEquipmentOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starting_equipment_group_id' => StartingEquipmentGroup::factory(),
            'label' => fake()->words(3, true),
        ];
    }
}
