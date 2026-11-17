<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Feat;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class FeatSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        $feats = [
            'Initié à la magie' => "Vous apprenez deux sorts mineurs et pouvez lancer un sort de niveau 1 une fois par jour sans dépenser d'emplacement.",
            'Vigilance' => "Vous ne pouvez pas être surpris tant que vous êtes conscient, et vous gagnez +5 à l'initiative.",
            'Robuste' => 'Vos points de vie maximum augmentent de 2 par niveau que vous possédez.',
            'Chanceux' => 'Vous disposez de points de chance à dépenser pour obtenir un avantage sur un jet.',
            'Guérisseur' => "Vous êtes formé pour stabiliser et soigner les blessés à l'aide d'une trousse de soins.",
            'Bagarreur' => 'Vous êtes formé au combat à mains nues et infligez plus de dégâts avec des armes improvisées.',
        ];

        foreach ($feats as $name => $description) {
            $this->seedOnce(Feat::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'category' => 'origin',
                'description' => $description,
            ]);
        }
    }
}
