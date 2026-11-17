<?php

namespace Database\Seeders\ruleset;

use App\Models\FeatureOption;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Level;
use App\Models\ruleset\Spell;
use App\Models\ruleset\SubclassLevel;
use App\Models\ruleset\Tool;
use App\Services\LevelUpFeatCatalog;
use App\Services\LevelUpOptions;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class LevelUpSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();
        $classes = CharacterClass::where('ruleset_id', $ruleset->id)->get()->keyBy('name');
        $catalog = json_decode(file_get_contents(__DIR__.'/data/level-up.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach ($classes as $class) {
            foreach ($catalog[$class->name] ?? [] as $number => $data) {
                $level = $class->classLevels()->whereHas('level', fn ($query) => $query->where('level_number', $number))->firstOrFail();
                $level->update(['progression' => $data['progression']]);
                foreach ($data['features'] as [$name, $description]) {
                    $name = match ($name) {
                        'Sursaut d’action' => "Sursaut d'action",
                        'Ruse' => 'Ruse',
                        default => $name,
                    };
                    if ($name === 'Capacité de domaine') {
                        // Remplacée par la véritable capacité de la sous-classe ci-dessous.
                        $level->features()->detach(Feature::where('ruleset_id', $ruleset->id)->where('name', $name)->pluck('id'));

                        continue;
                    }
                    $feature = Feature::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => $name], [
                        'description' => $description, 'feature_type' => 'class',
                    ]);
                    $level->features()->syncWithoutDetaching([$feature->id]);
                }
            }
        }
        foreach (json_decode(file_get_contents(__DIR__.'/data/features-2014.json'), true, flags: JSON_THROW_ON_ERROR) as $entry) {
            Feature::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => $entry['name']], [
                'description' => $entry['description'],
                'feature_type' => in_array($entry['index'], ['frenzy', 'evocation-savant', 'sculpt-spells', 'fast-hands', 'second-story-work', 'channel-divinity-preserve-life']) ? 'subclass' : 'class',
            ]);
        }
        $spells = collect();
        foreach (json_decode(file_get_contents(__DIR__.'/data/spells-2014.json'), true, flags: JSON_THROW_ON_ERROR) as $entry) {
            $classNames = $entry['classes'] ?? [];
            $index = $entry['index'];
            unset($entry['classes'], $entry['index']);
            $spell = Spell::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => $entry['name']], $entry);
            $spells->put($index, $spell);
            foreach ($classNames as $className) {
                $classes[$className]->spells()->syncWithoutDetaching([$spell->id]);
            }
        }
        Feat::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => LevelUpOptions::ABILITY_SCORE_IMPROVEMENT], [
            'category' => 'general',
            'description' => 'Augmentez une caractéristique de 2 ou deux caractéristiques de 1, sans dépasser 20. Si la Constitution augmente, les points de vie sont ajustés.',
        ]);
        foreach (LevelUpFeatCatalog::FEATS as $name => $description) {
            Feat::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => $name], [
                'category' => 'general', 'description' => $description,
            ]);
        }
        $subclassFeatures = [
            'Barbare' => [3 => ['Frénésie']],
            'Magicien' => [2 => ['Savant en évocation', 'Sculpture des sorts']],
            'Roublard' => [3 => ['Mains agiles', 'Monte-en-l’air']],
            'Clerc' => [2 => ['Préservation de la vie']],
        ];
        foreach ($subclassFeatures as $className => $levels) {
            foreach ($levels as $number => $names) {
                $this->subclassLevel($classes[$className], $number)->features()->syncWithoutDetaching(
                    Feature::where('ruleset_id', $ruleset->id)->whereIn('name', $names)->pluck('id')
                );
            }
        }
        foreach ([3 => ['lesser-restoration', 'spiritual-weapon'], 5 => ['beacon-of-hope', 'revivify']] as $number => $indices) {
            $this->subclassLevel($classes['Clerc'], $number)->spells()->syncWithoutDetaching(
                collect($indices)->map(fn ($index) => $spells[$index]->id)
            );
        }
        $maneuvers = Feature::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => 'Manœuvres de combat'], [
            'feature_type' => 'subclass', 'choice_count' => 3,
            'description' => 'Choisissez trois manœuvres différentes. Vous disposez de quatre dés de supériorité d8, récupérés après un repos court ou long. Une seule manœuvre peut être utilisée par attaque. Le DD de sauvegarde vaut 8 + bonus de maîtrise + modificateur de Force ou de Dextérité, au choix.',
        ]);
        foreach ([
            'Attaque dirigée' => 'Vous laissez passer une attaque pour qu’un allié utilise sa réaction et frappe avec un bonus aux dégâts.',
            'Attaque désarmante' => 'Après un coup avec une arme, la cible doit réussir une sauvegarde de Force ou lâcher un objet qu’elle tient.',
            'Frappe distrayante' => 'Après un coup avec une arme, la prochaine attaque contre cette cible bénéficie de l’avantage avant le début de votre prochain tour.',
            'Jeu de jambes évasif' => 'Quand vous vous déplacez, ajoutez le résultat d’un dé de supériorité à votre classe d’armure jusqu’à la fin du déplacement.',
            'Feinte' => 'Vous feintez une créature à portée avec une action bonus et gagnez l’avantage à votre prochaine attaque contre elle.',
            'Attaque provocatrice' => 'Après un coup avec une arme, la cible doit réussir une sauvegarde de Sagesse ou subir le désavantage à ses attaques contre vous.',
            'Attaque allongée' => 'Vous augmentez votre allonge pendant une attaque de mêlée pour gagner un bonus aux dégâts.',
            'Attaque de manœuvre' => 'Après un coup avec une arme, un allié peut se déplacer de la moitié de sa vitesse sans provoquer d’attaque d’opportunité.',
            'Parade' => 'En réaction, vous réduisez les dégâts d’une attaque de mêlée en dépensant un dé de supériorité.',
            'Attaque repoussante' => 'Après un coup avec une arme, une cible de taille grande ou moins est repoussée dans la direction opposée à vous.',
            'Ralliement' => 'Vous donnez des points de vie temporaires à un allié en dépensant une action bonus et un dé de supériorité.',
            'Attaque balayante' => 'Après un coup avec une arme de mêlée, une autre créature proche subit des dégâts si votre attaque la touche aussi.',
            'Attaque précise' => 'Vous ajoutez le résultat d’un dé de supériorité à votre jet d’attaque avant de déterminer les effets.',
            'Riposte' => 'En réaction, quand une créature vous rate au corps à corps, vous lui portez une attaque bonus avec votre arme.',
            'Attaque renversante' => 'Après un coup avec une arme, la cible doit réussir une sauvegarde de Force ou tomber à terre.',
            'Attaque menaçante' => 'Après un coup avec une arme, la cible doit réussir une sauvegarde de Sagesse ou être effrayée jusqu’à la fin de votre prochain tour.',
        ] as $name => $description) {
            FeatureOption::updateOrCreate(['feature_id' => $maneuvers->id, 'name' => $name], ['description' => $description]);
        }
        $student = Feature::updateOrCreate(['ruleset_id' => $ruleset->id, 'name' => 'Élève de guerre'], [
            'feature_type' => 'subclass', 'choice_count' => 1,
            'description' => 'Vous gagnez la maîtrise d’un type d’outils d’artisan de votre choix.',
        ]);
        foreach (['Fournitures d’alchimiste', 'Fournitures de brasseur', 'Outils de cartographe', 'Ustensiles de cuisinier', 'Outils de souffleur de verre', 'Outils de travailleur du cuir', 'Outils de maçon', 'Fournitures de peintre', 'Outils de potier', 'Outils de bricoleur', 'Outils de tisserand', 'Outils de sculpteur sur bois'] as $name) {
            Tool::firstOrCreate(['ruleset_id' => $ruleset->id, 'name' => $name], ['tool_category' => 'artisan']);
        }
        foreach (Tool::where('ruleset_id', $ruleset->id)->where('tool_category', 'artisan')->get() as $tool) {
            FeatureOption::updateOrCreate(['feature_id' => $student->id, 'name' => $tool->name], [
                'tool_id' => $tool->id,
                'description' => 'Vous ajoutez votre bonus de maîtrise aux tests utilisant ces outils.',
            ]);
        }
        $this->subclassLevel($classes['Guerrier'], 3)->features()->syncWithoutDetaching([$maneuvers->id, $student->id]);
    }

    private function subclassLevel(CharacterClass $class, int $number): SubclassLevel
    {
        return SubclassLevel::firstOrCreate([
            'subclass_id' => $class->subclasses()->firstOrFail()->id,
            'level_id' => Level::where('ruleset_id', $class->ruleset_id)->where('level_number', $number)->firstOrFail()->id,
        ]);
    }
}
