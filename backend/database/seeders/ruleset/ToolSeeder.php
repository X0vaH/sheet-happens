<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Tool;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

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

        foreach ($tools as $name => $category) {
            $this->seedOnce(Tool::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'tool_category' => $category,
            ]);
        }
    }
}
