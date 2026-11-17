<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    protected $model = Tool::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tools = [
            'Outils de forgeron' => 'artisan',
            'Outils de menuisier' => 'artisan',
            'Ustensiles de calligraphe' => 'artisan',
            'Outils de cordonnier' => 'artisan',
            'Outils de joaillier' => 'artisan',
            'Jeu de dés' => 'gaming_set',
            'Jeu de cartes' => 'gaming_set',
            'Cornemuse' => 'musical_instrument',
            'Luth' => 'musical_instrument',
            'Tambour' => 'musical_instrument',
            'Kit de déguisement' => 'kit',
            'Kit d\'empoisonneur' => 'kit',
            'Outils de voleur' => 'kit',
            'Trousse de soins' => 'kit',
            'Kit de navigateur' => 'kit',
            'Véhicules terrestres' => 'vehicle',
            'Véhicules nautiques' => 'vehicle',
        ];

        $name = fake()->randomElement(array_keys($tools));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'tool_category' => $tools[$name],
        ];
    }
}
