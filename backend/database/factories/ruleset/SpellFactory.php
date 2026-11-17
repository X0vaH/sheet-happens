<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Spell;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spell>
 */
class SpellFactory extends Factory
{
    protected $model = Spell::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $spells = [
            'Trait de feu' => [0, 'evocation', '1 action', '36 mètres', 'Instantanée', false, false, true, true, false, null],
            'Lumière' => [0, 'evocation', '1 action', 'Contact', '1 heure', false, false, true, false, true, 'un ver luisant ou de la mousse phosphorescente'],
            'Prestidigitation' => [0, 'transmutation', '1 action', '3 mètres', "Jusqu'à 1 heure", false, false, true, true, false, null],
            'Main du mage' => [0, 'conjuration', '1 action', '9 mètres', '1 minute', false, false, true, true, false, null],
            'Thaumaturgie' => [0, 'divination', '1 action', '9 mètres', "Jusqu'à 1 minute", false, false, true, false, false, null],
            'Projectile magique' => [1, 'evocation', '1 action', '36 mètres', 'Instantanée', false, false, true, true, false, null],
            'Bouclier' => [1, 'abjuration', '1 réaction', 'Personnelle', '1 round', false, false, true, true, false, null],
            'Soins' => [1, 'evocation', '1 action', 'Contact', 'Instantanée', false, false, true, true, false, null],
            'Bénédiction' => [1, 'enchantment', '1 action', '9 mètres', "Concentration, jusqu'à 1 minute", true, false, true, true, true, "une pincée d'eau bénite"],
            'Détection de la magie' => [1, 'divination', '1 action', 'Personnelle', "Concentration, jusqu'à 10 minutes", true, true, true, true, false, null],
        ];

        $name = fake()->randomElement(array_keys($spells));
        [$level, $school, $castingTime, $range, $duration, $concentration, $ritual, $verbal, $somatic, $material, $materialDescription] = $spells[$name];

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'spell_level' => $level,
            'school' => $school,
            'casting_time' => $castingTime,
            'range' => $range,
            'duration' => $duration,
            'concentration' => $concentration,
            'ritual' => $ritual,
            'verbal' => $verbal,
            'somatic' => $somatic,
            'material' => $material,
            'material_description' => $materialDescription,
            'description' => fake()->sentence(15),
        ];
    }
}
