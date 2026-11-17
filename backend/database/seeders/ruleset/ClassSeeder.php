<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Ability;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ClassSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        // Progression officielle du bonus de maîtrise, niveaux 1 à 20.
        $levels = $this->seedManyOnce(Level::class, ['ruleset_id', 'level_number'], array_map(fn (int $n): array => [
            'ruleset_id' => $ruleset->id,
            'level_number' => $n,
            'proficiency_bonus' => 2 + intdiv($n - 1, 4),
        ], range(1, 20)))->keyBy('level_number');

        $abilities = Ability::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name')->toBase();
        $features = Feature::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name')->toBase();

        $classes = [
            'Barbare' => [
                'hit_die' => 12,
                'description' => 'Barbare porté par sa rage brute et son violence incontrôlée.',
                'saves' => ['Force', 'Constitution'],
                'skill_choice_count' => 2,
                'cantrip_choice_count' => 0,
                'spell_choice_count' => 0,
                'features' => [
                    1 => ['Rage', 'Défense sans armure'],
                    2 => ['Attaque téméraire', 'Sens du danger'],
                ],
                'subclass' => [
                    'name' => 'Voie du Berserker',
                    'description' => 'Une voie de barbarie qui laisse la rage guider chaque coup.',
                    'unlock' => 3,
                    'feature' => 'Frénésie',
                ],
            ],
            'Clerc' => [
                'hit_die' => 8,
                'description' => 'Clerc qui puise sa puissance dans la magie sacrée de sa divinité.',
                'saves' => ['Sagesse', 'Charisme'],
                'skill_choice_count' => 2,
                'cantrip_choice_count' => 3,
                'spell_choice_count' => 4,
                'features' => [
                    1 => ['Incantation', 'Domaine divin'],
                ],
                'subclass' => [
                    'name' => 'Domaine de la Vie',
                    'description' => 'Un domaine axé sur les soins, la protection et la vie.',
                    'unlock' => 1,
                    'feature' => 'Disciple de la vie',
                ],
            ],
            'Guerrier' => [
                'hit_die' => 10,
                'description' => 'Guerrier expert dans l’usage des armes, des armures et du combat.',
                'saves' => ['Force', 'Constitution'],
                'skill_choice_count' => 2,
                'cantrip_choice_count' => 0,
                'spell_choice_count' => 0,
                'features' => [
                    1 => ['Style de combat', 'Récupération au combat'],
                    2 => ['Sursaut d\'action'],
                ],
                'subclass' => [
                    'name' => 'Maître de guerre',
                    'description' => 'Une tradition martiale fondée sur la stratégie et les manœuvres.',
                    'unlock' => 3,
                    'feature' => 'Manœuvres de combat',
                ],
            ],
            'Magicien' => [
                'hit_die' => 6,
                'description' => 'Magicien qui maîtrise les secrets et le pouvoir de l’arcanes.',
                'saves' => ['Intelligence', 'Sagesse'],
                'skill_choice_count' => 2,
                'cantrip_choice_count' => 3,
                'spell_choice_count' => 6,
                'features' => [
                    1 => ['Incantation', 'Récupération arcanique'],
                ],
                'subclass' => [
                    'name' => 'École d\'évocation',
                    'description' => 'Une tradition spécialisée dans la puissance et la destruction magiques.',
                    'unlock' => 2,
                    'feature' => 'Savant en évocation',
                ],
            ],
            'Roublard' => [
                'hit_die' => 8,
                'description' => 'Roublard discret, agile et redoutable au combat.',
                'saves' => ['Dextérité', 'Intelligence'],
                'skill_choice_count' => 4,
                'cantrip_choice_count' => 0,
                'spell_choice_count' => 0,
                'features' => [
                    1 => ['Expertise', 'Vol furtif', 'Argot des voleurs'],
                    2 => ['Ruse'],
                ],
                'subclass' => [
                    'name' => 'Voleur',
                    'description' => 'Un archétype centré sur la discrétion, la précision et l’agilité.',
                    'unlock' => 3,
                    'feature' => 'Mains agiles',
                ],
            ],
        ];

        foreach ($classes as $name => $data) {
            $this->createClass($ruleset, $name, $data, $levels, $abilities, $features);
        }
    }

    /**
     * Crée une classe avec ses jets de sauvegarde, ses 20 niveaux, ses aptitudes par niveau et sa sous-classe.
     *
     * @param  array<string, mixed>  $data
     * @param  Collection<int, Level>  $levels  Niveaux du ruleset, indexés par numéro de niveau.
     * @param  Collection<string, Ability>  $abilities  Caractéristiques du ruleset, indexées par nom.
     * @param  Collection<string, Feature>  $features  Aptitudes du ruleset, indexées par nom.
     */
    private function createClass(Ruleset $ruleset, string $name, array $data, Collection $levels, Collection $abilities, Collection $features): void
    {
        $class = $this->seedOnce(CharacterClass::class, ['ruleset_id', 'name'], [
            'ruleset_id' => $ruleset->id,
            'name' => $name,
            'hit_die' => $data['hit_die'],
            'description' => $data['description'],
            'skill_choice_count' => $data['skill_choice_count'],
            'cantrip_choice_count' => $data['cantrip_choice_count'],
            'spell_choice_count' => $data['spell_choice_count'],
        ]);

        $class->update(['description' => $data['description']]);

        $class->savingThrows()->syncWithoutDetaching($abilities->only($data['saves'])->pluck('id'));

        $classLevels = $this->seedManyOnce(ClassLevel::class, ['class_id', 'level_id'], $levels->map(fn (Level $level): array => [
            'class_id' => $class->id,
            'level_id' => $level->id,
        ])->values()->all())->keyBy('level_id');

        foreach ($data['features'] as $levelNumber => $featureNames) {
            $classLevels[$levels[$levelNumber]->id]->features()->syncWithoutDetaching($features->only($featureNames)->pluck('id'));
        }

        $subclassData = $data['subclass'];

        $subclass = Subclass::where('name', $subclassData['name'])
            ->whereHas('classLevel', fn ($query) => $query->where('class_id', $class->id))->first();
        $subclass ??= new Subclass;
        $previousLevelId = $subclass->exists ? $subclass->classLevel->level_id : null;
        $subclass->fill([
            'class_level_id' => $classLevels[$levels[$subclassData['unlock']]->id]->id,
            'name' => $subclassData['name'],
            'description' => $subclassData['description'],
        ])->save();

        $subclassLevel = $this->seedOnce(SubclassLevel::class, ['subclass_id', 'level_id'], [
            'subclass_id' => $subclass->id,
            'level_id' => $levels[$subclassData['unlock']]->id,
        ]);

        $feature = $features->get($subclassData['feature']) ?? throw (new ModelNotFoundException)->setModel(Feature::class);
        $subclassLevel->features()->syncWithoutDetaching($feature->id);
        // Seule la capacité de déblocage change de niveau ; les autres relations restent en place.
        if ($previousLevelId && $previousLevelId !== $subclassLevel->level_id) {
            $previous = SubclassLevel::where('subclass_id', $subclass->id)->where('level_id', $previousLevelId)->first();
            $previous?->features()->detach($feature->id);
        }
    }
}
