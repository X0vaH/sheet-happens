<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Language;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    use SeedsOnce;

    /**
     * @var array<string, string> nom => description
     */
    public const DEFINITIONS = [
        'Commun' => 'Langue la plus répandue, utilisée pour communiquer entre les différents peuples.',
        'Draconique' => 'Langue ancienne des Dragons, également utilisée par les Drakéides et certains érudits.',
        'Elfique' => 'Langue des Elfes, fluide et mélodieuse, possédant une riche tradition littéraire.',
        'Géant' => 'Langue des Géants et de plusieurs créatures apparentées, aux sonorités puissantes.',
        'Gnome' => 'Langue des Gnomes, utilisée notamment dans leurs communautés et leurs écrits techniques.',
        'Gobelin' => 'Langue utilisée principalement par les Gobelins, Hobgobelins et autres peuples gobelinoïdes.',
        'Halfelin' => 'Langue traditionnelle des Halfelins, surtout parlée au sein de leurs communautés.',
        'Nain' => 'Langue traditionnelle des Nains, au ton robuste et aux sonorités marquées.',
        'Orc' => 'Langue des Orcs, aux sonorités fortes et directes.',
        'Abyssal' => 'Langue liée aux démons et aux créatures originaires des Abysses.',
        'Céleste' => 'Langue ancienne associée aux êtres célestes et aux plans supérieurs.',
        'Infernal' => 'Langue structurée des Diables et des créatures liées aux Neuf Enfers.',
        'Primordial' => 'Langue ancienne associée aux forces élémentaires et aux créatures des plans élémentaires.',
        'Sylvestre' => 'Langue des nombreuses créatures féeriques, fortement associée à la Féerie.',
        'Profond' => 'Langue étrange associée à certaines créatures mystérieuses venues des profondeurs ou d’ailleurs.',
        'Sous-commun' => 'Langue utilisée comme moyen de communication entre différents peuples de l’Outreterre.',
    ];

    public function run(): void
    {
        $ruleset = $this->ruleset();

        foreach (self::DEFINITIONS as $name => $description) {
            $this->seedOnce(Language::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'description' => $description,
            ]);
        }
    }
}
