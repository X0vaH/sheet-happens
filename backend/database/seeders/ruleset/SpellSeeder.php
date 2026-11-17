<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class SpellSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

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
            'Assistance' => [0, 'divination', '1 action', 'Contact', "Concentration, jusqu'à 1 minute", true, false, true, false, true, 'un bâtonnet d’encens'],
            'Résistance' => [0, 'abjuration', '1 action', 'Contact', "Concentration, jusqu'à 1 minute", true, false, true, true, true, 'un petit morceau de bois brûlé'],
            'Stabilisation' => [0, 'necromancy', '1 action', '9 mètres', 'Instantanée', false, false, true, true, false, null],
            'Injonction' => [1, 'enchantment', '1 action', '18 mètres', 'Instantanée', false, false, true, false, false, null],
            'Rayon de givre' => [0, 'evocation', '1 action', '18 mètres', 'Instantanée', false, false, true, true, false, null],
            'Message' => [0, 'transmutation', '1 action', '36 mètres', '1 round', false, false, true, true, false, null],
            'Identification' => [1, 'divination', '1 minute', 'Contact', 'Instantanée', false, true, true, true, true, 'une perle d’une valeur de 100 po et une plume de chouette'],
            'Chute de plumes' => [1, 'transmutation', '1 réaction', '18 mètres', '1 minute', false, false, true, false, false, null],
            // Sorts supplémentaires pour que le nombre de sorts seedés couvre les limites de
            // choix du Clerc et du Magicien (cantrip_choice_count / spell_choice_count).
            'Flamme sacrée' => [0, 'evocation', '1 action', '18 mètres', 'Instantanée', false, false, true, true, false, null],
            'Sanctuaire' => [1, 'abjuration', '1 action bonus', '9 mètres', '1 minute', false, false, true, true, true, 'un petit miroir en argent'],
            'Sommeil' => [1, 'enchantment', '1 action', '18 mètres', '1 minute', false, false, true, true, true, 'une pincée de sable fin, des pétales de rose ou un criquet'],
            'Armure du mage' => [1, 'abjuration', '1 action', 'Contact', '8 heures', false, false, true, true, true, 'un morceau de cuir tanné'],
            'Charme-personne' => [1, 'enchantment', '1 action', '9 mètres', '1 heure', false, false, true, true, false, null],
        ];

        /** @var array<string, Spell> $created */
        $created = [];

        foreach ($spells as $name => $attributes) {
            [$level, $school, $castingTime, $range, $duration, $concentration, $ritual, $verbal, $somatic, $material, $materialDescription] = $attributes;

            $created[$name] = $this->seedOnce(Spell::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
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
            ]);
        }

        $clerc = CharacterClass::query()->where('ruleset_id', $ruleset->id)->where('name', 'Clerc')->firstOrFail();
        $magicien = CharacterClass::query()->where('ruleset_id', $ruleset->id)->where('name', 'Magicien')->firstOrFail();

        $clerc->spells()->syncWithoutDetaching(collect(['Lumière', 'Thaumaturgie', 'Flamme sacrée', 'Assistance', 'Résistance', 'Soins', 'Bénédiction', 'Détection de la magie', 'Sanctuaire', 'Stabilisation', 'Injonction'])
            ->map(fn (string $name) => $created[$name]->id));

        $magicien->spells()->syncWithoutDetaching(collect(['Trait de feu', 'Prestidigitation', 'Main du mage', 'Rayon de givre', 'Message', 'Projectile magique', 'Bouclier', 'Détection de la magie', 'Sommeil', 'Armure du mage', 'Charme-personne', 'Identification', 'Chute de plumes'])
            ->map(fn (string $name) => $created[$name]->id));

        // Projectile magique est un sort emblématique appris automatiquement par le Magicien au niveau 1.
        $magicienNiveau1 = ClassLevel::query()
            ->whereHas('level', fn ($query) => $query->where('level_number', 1))
            ->where('class_id', $magicien->id)
            ->firstOrFail();
        $magicienNiveau1->spells()->syncWithoutDetaching($created['Projectile magique']->id);

        // Sorts de domaine de la Vie : accordés automatiquement dès le niveau 1 au Clerc de ce domaine.
        $domaineDeLaVie = Subclass::query()
            ->where('name', 'Domaine de la Vie')
            ->whereHas('classLevel.characterClass', fn ($query) => $query->where('ruleset_id', $ruleset->id))
            ->firstOrFail();
        $domaineDeLaVie->spells()->syncWithoutDetaching(collect(['Soins', 'Bénédiction'])->map(fn (string $name) => $created[$name]->id));

        $domaineDeLaVieNiveau1 = SubclassLevel::query()
            ->where('subclass_id', $domaineDeLaVie->id)
            ->whereHas('level', fn ($query) => $query->where('level_number', 1))
            ->firstOrFail();
        $domaineDeLaVieNiveau1->spells()->syncWithoutDetaching(collect(['Soins', 'Bénédiction'])->map(fn (string $name) => $created[$name]->id));
    }
}
