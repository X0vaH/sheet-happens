<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Feature;
use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $features = [
            // Traits raciaux (espèce)
            'Vision dans le noir' => 'species',
            'Résilience naine' => 'species',
            'Ascendance féerique' => 'species',
            'Transe' => 'species',
            'Ruse gnome' => 'species',
            'Chanceux' => 'species',
            'Brave' => 'species',
            'Ascendance draconique' => 'species',
            'Souffle du dragon' => 'species',
            'Polyvalence' => 'species',
            'Endurance implacable' => 'species',
            'Résistance infernale' => 'species',
            'Legs infernal' => 'species',
            'Ingéniosité' => 'species',
            // Aptitudes de classe
            'Rage' => 'class',
            'Défense sans armure' => 'class',
            'Attaque téméraire' => 'class',
            'Sens du danger' => 'class',
            'Incantation' => 'class',
            'Domaine divin' => 'class',
            'Style de combat' => 'class',
            'Récupération au combat' => 'class',
            'Sursaut d\'action' => 'class',
            'Récupération arcanique' => 'class',
            'Expertise' => 'class',
            'Vol furtif' => 'class',
            'Argot des voleurs' => 'class',
            'Ruse' => 'class',
            // Aptitudes de sous-classe
            'Fureur implacable' => 'subclass',
            'Disciple de la vie' => 'subclass',
            'Manœuvres de combat' => 'subclass',
            'Savant en évocation' => 'subclass',
            'Mains agiles' => 'subclass',
            // Aptitudes générales
            'Inspiration héroïque' => 'general',
            'Multiclassage' => 'general',
        ];

        $name = fake()->randomElement(array_keys($features));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'feature_type' => $features[$name],
            'description' => fake()->sentence(12),
        ];
    }
}
