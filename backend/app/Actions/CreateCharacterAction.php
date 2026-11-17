<?php

namespace App\Actions;

use App\Exceptions\CharacterLimitReachedException;
use App\Models\Character;
use App\Models\CharacterAbility;
use App\Models\CharacterClass;
use App\Models\CharacterDetail;
use App\Models\CharacterFeature;
use App\Models\CharacterFeatureChoice;
use App\Models\CharacterInventory;
use App\Models\CharacterLanguage;
use App\Models\CharacterLevelClass;
use App\Models\CharacterOrigin;
use App\Models\CharacterSkillProficiency;
use App\Models\CharacterSpell;
use App\Models\CharacterTool;
use App\Models\ruleset\Ability;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\StartingEquipmentOption;
use App\Models\User;
use App\Support\AbilityScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCharacterAction
{
    /**
     * Crée un personnage niveau 1 complet et tout ce qui en dépend, dans une seule
     * transaction : soit tout est sauvegardé, soit rien ne l'est.
     *
     * @param  array<string, mixed>  $data  Payload validé de StoreCharacterRequest.
     *
     * @throws CharacterLimitReachedException Si l'utilisateur a déjà Character::MAX_PER_USER personnages.
     * @throws ValidationException Si l'emplacement demandé (`order_index`) est déjà occupé.
     */
    public function execute(User $user, Ruleset $ruleset, array $data): Character
    {
        return DB::transaction(function () use ($user, $ruleset, $data): Character {
            // Verrouille la ligne de l'utilisateur pour sérialiser les créations concurrentes :
            // sans cela, deux requêtes simultanées pourraient toutes deux voir 4 personnages
            // et en créer un 5e et un 6e.
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            if (Character::query()->where('user_id', $user->id)->count() >= Character::MAX_PER_USER) {
                throw new CharacterLimitReachedException;
            }

            $orderIndex = $this->orderIndex($user, $data['order_index'] ?? null);
            $hitPoints = $this->startingHitPoints($ruleset, $data);

            $character = Character::create([
                'user_id' => $user->id,
                'ruleset_id' => $ruleset->id,
                'name' => $data['name'],
                'current_health_points' => $hitPoints,
                'max_health_points' => $hitPoints,
                'alignment' => $data['alignment'] ?? null,
                'notes' => $data['notes'] ?? null,
                'order_index' => $orderIndex,
            ]);

            CharacterDetail::create(['character_id' => $character->id]);

            CharacterOrigin::create([
                'character_id' => $character->id,
                'specie_id' => $data['species_id'],
                'background_id' => $data['background_id'],
            ]);

            $primary = CharacterClass::create([
                'character_id' => $character->id,
                'class_id' => $data['class_id'],
                'subclass_id' => $data['subclass_id'] ?? null,
                'first_taken_at_character_level' => 1,
            ]);

            $level = $character->levels()->create(['character_level' => 1, 'experience_points' => 0]);
            CharacterLevelClass::create(['character_class_id' => $primary->id, 'character_level_id' => $level->id, 'resulting_class_level' => 1]);

            foreach ($data['abilities'] as $ability) {
                CharacterAbility::create([
                    'character_id' => $character->id,
                    'ability_id' => $ability['ability_id'],
                    'score' => $ability['score'],
                ]);
            }

            foreach ($data['skill_proficiencies'] ?? [] as $skill) {
                CharacterSkillProficiency::create([
                    'character_id' => $character->id,
                    'skill_id' => $skill['skill_id'],
                    'proficiency_level' => $skill['proficiency_level'],
                ]);
            }

            foreach ($data['languages'] ?? [] as $language) {
                CharacterLanguage::create([
                    'character_id' => $character->id,
                    'language_id' => $language['language_id'],
                    'source' => $language['source'],
                ]);
            }

            foreach ($data['tools'] ?? [] as $tool) {
                CharacterTool::create([
                    'character_id' => $character->id,
                    'tool_id' => $tool['tool_id'],
                    'proficiency_level' => $tool['proficiency_level'],
                ]);
            }

            foreach ($data['feature_choices'] ?? [] as $choice) {
                $characterFeature = CharacterFeature::create([
                    'character_id' => $character->id,
                    'feature_id' => $choice['feature_id'],
                    'acquired_at_character_level' => 1,
                ]);

                if (! empty($choice['feature_option_id'])) {
                    CharacterFeatureChoice::create([
                        'character_feature_id' => $characterFeature->id,
                        'feature_option_id' => $choice['feature_option_id'],
                    ]);
                }
            }

            foreach ($data['spells'] ?? [] as $spell) {
                CharacterSpell::create([
                    'character_id' => $character->id,
                    'spell_id' => $spell['spell_id'],
                    'acquired_at_character_level' => 1,
                ]);
            }

            foreach ($this->inventoryTotals($data) as $itemId => $quantity) {
                CharacterInventory::create([
                    'character_id' => $character->id,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                ]);
            }

            return $character;
        });
    }

    /**
     * Additionne l'inventaire libre (`inventory`) et les objets résolus depuis les choix
     * d'équipement de départ (`starting_equipment_choices`), regroupés par objet. Un même
     * objet peut apparaître dans les deux (ou dans deux options choisies) : character_inventory
     * a une contrainte unique (character_id, item_id), donc il faut agréger avant de créer les
     * lignes plutôt que boucler naïvement dessus.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, int> Quantité totale par item_id.
     */
    private function inventoryTotals(array $data): array
    {
        $totals = [];

        foreach ($data['inventory'] ?? [] as $item) {
            $totals[$item['item_id']] = ($totals[$item['item_id']] ?? 0) + $item['quantity'];
        }

        $optionIds = collect($data['starting_equipment_choices'] ?? [])->pluck('starting_equipment_option_id');

        if ($optionIds->isNotEmpty()) {
            $options = StartingEquipmentOption::query()->with('items')->whereIn('id', $optionIds)->get();

            foreach ($options as $option) {
                foreach ($option->items as $item) {
                    $totals[$item->id] = ($totals[$item->id] ?? 0) + $item->pivot->quantity;
                }
            }
        }

        return $totals;
    }

    /**
     * Emplacement du nouveau personnage dans le carrousel : celui demandé, ou le premier libre
     * s'il n'y en a pas. Revérifié ici, sous le verrou de l'utilisateur, car deux créations
     * simultanées peuvent toutes deux passer la règle `unique` de StoreCharacterRequest.
     * Appelé après le contrôle de la limite, il reste donc toujours un emplacement libre.
     *
     * @throws ValidationException
     */
    private function orderIndex(User $user, ?int $requested): int
    {
        $taken = Character::query()->where('user_id', $user->id)->pluck('order_index')->map(fn ($index) => (int) $index)->all();

        if ($requested === null) {
            return collect(range(0, Character::MAX_PER_USER - 1))
                ->first(fn (int $index) => ! in_array($index, $taken, true));
        }

        if (in_array($requested, $taken, true)) {
            throw ValidationException::withMessages(['order_index' => 'Cet emplacement est déjà occupé par un autre personnage.']);
        }

        return $requested;
    }

    /**
     * PV de départ niveau 1 (règle D&D 5e 2024) : dé de vie maximal de la classe +
     * modificateur de Constitution, jamais en dessous de 1. Calculé côté serveur plutôt
     * que reçu du frontend puisque c'est une valeur dérivée, pas un choix du joueur.
     *
     * @param  array<string, mixed>  $data
     */
    private function startingHitPoints(Ruleset $ruleset, array $data): int
    {
        $hitDie = ClassDefinition::query()->findOrFail($data['class_id'])->hit_die;

        $constitutionId = Ability::query()
            ->where('ruleset_id', $ruleset->id)
            ->where('abbreviation', 'CON')
            ->value('id');

        $constitutionScore = collect($data['abilities'])
            ->firstWhere('ability_id', $constitutionId)['score'] ?? 10;

        $modifier = AbilityScore::modifier($constitutionScore);

        return max(1, $hitDie + $modifier);
    }
}
