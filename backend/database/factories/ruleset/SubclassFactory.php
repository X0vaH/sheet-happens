<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Subclass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subclass>
 */
class SubclassFactory extends Factory
{
    protected $model = Subclass::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subclasses = [
            'Voie du Berserker' => 'Une tradition de barbares qui laissent leur rage guider chaque coup, sans retenue.',
            'Domaine de la Vie' => 'Un domaine divin centré sur la préservation de la vie et les soins.',
            'Maître de guerre' => 'Une tradition martiale qui repose sur des manœuvres tactiques de combat.',
            'École d\'évocation' => 'Une tradition arcanique spécialisée dans les sorts destructeurs et énergétiques.',
            'Voleur' => 'Un archétype de roublard expert en subtilisation et en agilité manuelle.',
        ];

        $name = fake()->randomElement(array_keys($subclasses));

        return [
            'class_level_id' => ClassLevel::query()->inRandomOrder()->value('id') ?? ClassLevel::factory()->create()->id,
            'name' => $name,
            'description' => $subclasses[$name],
        ];
    }
}
