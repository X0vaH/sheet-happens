<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Feature;
use App\Models\ruleset\Language;
use App\Models\ruleset\Species;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        // Langues fixes uniquement (Commun + langue ancestrale) : Humain et Demi-elfe ont
        // normalement en plus une langue au choix du joueur, non modélisée pour l'instant.
        $species = [
            'Humain' => ['medium', 9, "Adaptables et ambitieux, les humains sont l'espèce la plus répandue de Féerune.", ['Ingéniosité'], ['Commun']],
            'Elfe' => ['medium', 9, "Peuple féerique gracieux, doté d'une longue espérance de vie et d'une affinité avec la magie.", ['Ascendance féerique', 'Transe'], ['Commun', 'Elfique']],
            'Nain' => ['medium', 7.5, 'Robustes et endurants, les nains vivent dans des cités creusées à même la roche.', ['Résilience naine', 'Vision dans le noir'], ['Commun', 'Nain']],
            'Halfelin' => ['small', 7.5, 'Petit peuple discret et chanceux, réputé pour son courage face à l\'adversité.', ['Chanceux', 'Brave'], ['Commun', 'Halfelin']],
            'Draconien' => ['medium', 9, "Descendants des dragons, dotés d'une ascendance élémentaire et d'un souffle destructeur.", ['Ascendance draconique', 'Souffle du dragon'], ['Commun', 'Draconique']],
            'Gnome' => ['small', 7.5, 'Curieux et inventifs, les gnomes ont une résistance naturelle à la magie.', ['Ruse gnome', 'Vision dans le noir'], ['Commun', 'Gnome']],
            'Demi-elfe' => ['medium', 9, "Nés de l'union entre humains et elfes, ils allient la polyvalence des deux peuples.", ['Polyvalence', 'Ascendance féerique'], ['Commun', 'Elfique']],
            'Demi-orc' => ['medium', 9, "Descendants d'orcs et d'humains, réputés pour leur endurance hors du commun.", ['Endurance implacable', 'Vision dans le noir'], ['Commun', 'Orc']],
            'Tieffelin' => ['medium', 9, 'Marqués par un héritage infernal, ils portent cornes et queue en héritage de leurs ancêtres.', ['Résistance infernale', 'Legs infernal'], ['Commun', 'Infernal']],
        ];

        foreach ($species as $name => [$size, $speed, $description, $featureNames, $languageNames]) {
            $specie = $this->seedOnce(Species::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'size_category' => $size,
                'base_speed' => $speed,
                'description' => $description,
            ]);

            $featureIds = Feature::query()->where('ruleset_id', $ruleset->id)->whereIn('name', $featureNames)->pluck('id');
            $specie->features()->syncWithoutDetaching($featureIds);

            $languageIds = Language::query()->where('ruleset_id', $ruleset->id)->whereIn('name', $languageNames)->pluck('id');
            $specie->languages()->syncWithoutDetaching($languageIds);
        }
    }
}
