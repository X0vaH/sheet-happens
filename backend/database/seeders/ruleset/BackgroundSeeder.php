<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Tool;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class BackgroundSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        $backgrounds = [
            'Acolyte' => [
                'description' => "Vous avez passé votre jeunesse au service d'un temple, assistant les prêtres dans les rites sacrés.",
                'abilities' => ['Intelligence', 'Sagesse', 'Charisme'],
                'skills' => ['Perspicacité', 'Religion'],
                'tools' => ['Ustensiles de calligraphe'],
                'feats' => ['Initié à la magie'],
                'language_choice_count' => 2,
            ],
            'Soldat' => [
                'description' => 'Vous avez servi dans une armée, appris la discipline militaire et le maniement des armes.',
                'abilities' => ['Force', 'Dextérité', 'Constitution'],
                'skills' => ['Athlétisme', 'Intimidation'],
                'tools' => ['Véhicules terrestres'],
                'feats' => ['Vigilance'],
                'language_choice_count' => 0,
            ],
            'Sage' => [
                'description' => 'Vous avez passé des années à étudier les arcanes du savoir dans une bibliothèque ou une université.',
                'abilities' => ['Intelligence', 'Sagesse', 'Charisme'],
                'skills' => ['Arcanes', 'Histoire'],
                'tools' => ['Ustensiles de calligraphe'],
                'feats' => ['Initié à la magie'],
                'language_choice_count' => 2,
            ],
            'Criminel' => [
                'description' => 'Vous avez vécu en marge de la loi, maîtrisant la discrétion et les activités illicites.',
                'abilities' => ['Dextérité', 'Constitution', 'Intelligence'],
                'skills' => ['Discrétion', 'Tromperie'],
                'tools' => ['Outils de voleur'],
                'feats' => ['Chanceux'],
                'language_choice_count' => 0,
            ],
            'Guide' => [
                'description' => 'Vous avez grandi loin de la civilisation, familier des terres sauvages et de la survie.',
                'abilities' => ['Force', 'Dextérité', 'Sagesse'],
                'skills' => ['Survie', 'Athlétisme'],
                'tools' => ['Véhicules terrestres'],
                'feats' => ['Robuste'],
                'language_choice_count' => 1,
            ],
        ];

        foreach ($backgrounds as $name => $data) {
            $background = $this->seedOnce(Background::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'description' => $data['description'],
                'language_choice_count' => $data['language_choice_count'],
            ]);

            $background->abilities()->syncWithoutDetaching(
                Ability::query()->where('ruleset_id', $ruleset->id)->whereIn('name', $data['abilities'])->pluck('id')
            );

            $background->skills()->syncWithoutDetaching(
                Skill::query()->where('ruleset_id', $ruleset->id)->whereIn('name', $data['skills'])->pluck('id')
            );

            $background->tools()->syncWithoutDetaching(
                Tool::query()->where('ruleset_id', $ruleset->id)->whereIn('name', $data['tools'])->pluck('id')
            );

            $background->feats()->syncWithoutDetaching(
                Feat::query()->where('ruleset_id', $ruleset->id)->whereIn('name', $data['feats'])->pluck('id')
            );
        }
    }
}
