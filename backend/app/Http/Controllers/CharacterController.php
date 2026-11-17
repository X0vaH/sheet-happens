<?php

namespace App\Http\Controllers;

use App\Actions\CreateCharacterAction;
use App\Http\Requests\StoreCharacterRequest;
use App\Models\Character;
use App\Models\ruleset\Ability;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use App\Services\CharacterStats;
use Database\Seeders\ruleset\RulesetSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controleur servant à la consultation, à la création et à la suppression des personnages d'un utilisateur
 */
class CharacterController extends Controller
{
    /**
     * Liste des personnages appartenant à l'utilisateur authentifié.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $characters = $request->user()
            ->characters()
            ->with([
                'origin.specie',
                'origin.background',
                'classes.classDefinition',
                'classes.subclass',
                'level',
                'levels',
                'features.feature',
                'features.choices.featureOption',
            ])
            ->orderBy('order_index')
            ->orderBy('id')
            ->get();

        foreach ($characters as $character) {
            $stats = new CharacterStats($character);
            $character->setAttribute('next_level', $stats->nextLevel());
            $character->setAttribute('fighting_style', $stats->fightingStyle());
        }

        return response()->json(['characters' => $characters]);
    }

    /**
     * Détail d'un personnage appartenant à l'utilisateur authentifié.
     *
     * @return JsonResponse
     */
    public function show(Request $request, Character $character)
    {
        abort_unless($character->user_id === $request->user()->id, 404, 'Ressource non trouvée.');

        $character->load([
            'origin.specie',
            'origin.background',
            'detail',
            'classes.classDefinition.savingThrows',
            'classes.subclass',
            'level',
            'levels',
            'abilities.ability',
            'abilityIncreases.ability',
            'skillProficiencies.skill',
            'languages.language',
            'tools.tool',
            'features.feature',
            'features.choices.featureOption',
            'feats.feat',
            'spells.spell',
            'spellSlots',
            'inventory.item.weapon',
            'inventory.item.armor',
        ]);

        $stats = new CharacterStats($character);

        $character->setAttribute('armor_class', $stats->armorClass());
        $character->setAttribute('initiative', $stats->initiative());
        $character->setAttribute('proficiency_bonus', $stats->proficiencyBonus());
        $character->setAttribute('next_level', $stats->nextLevel());
        $character->setAttribute('fighting_style', $stats->fightingStyle());

        foreach ($character->abilities as $ability) {
            $ability->setAttribute('effective_score', $stats->effectiveScore($ability->ability_id));
        }

        $proficiencyLevels = $character->skillProficiencies->pluck('proficiency_level', 'skill_id');

        $skills = Skill::query()
            ->with('ability')
            ->where('ruleset_id', $character->ruleset_id)
            ->orderBy('id')
            ->get()
            ->map(function (Skill $skill) use ($proficiencyLevels, $stats) {
                $proficiencyLevel = $proficiencyLevels->get($skill->id, 'none');

                return [
                    'name' => $skill->name,
                    'ability' => $skill->ability->name,
                    'proficient' => $proficiencyLevel !== 'none',
                    'bonus' => $stats->skillBonus($skill->ability->name, $proficiencyLevel),
                ];
            });
        $character->setAttribute('skills', $skills);

        $proficientAbilityIds = $character->classes
            ->flatMap(fn ($class) => $class->classDefinition->savingThrows->pluck('id'));

        $savingThrows = Ability::query()
            ->where('ruleset_id', $character->ruleset_id)
            ->orderBy('id')
            ->get()
            ->map(function (Ability $ability) use ($proficientAbilityIds, $stats) {
                $proficient = $proficientAbilityIds->contains($ability->id);

                return [
                    'ability' => $ability->name,
                    'proficient' => $proficient,
                    'bonus' => $stats->savingThrowBonus($ability->name, $proficient),
                ];
            });
        $character->setAttribute('saving_throws', $savingThrows);

        foreach ($character->inventory as $entry) {
            if ($entry->item->weapon) {
                $entry->item->weapon->setAttribute('attack_bonus', $stats->weaponAttackBonus($entry->item->weapon));
            }
        }

        return response()->json(['character' => $character]);
    }

    public function store(StoreCharacterRequest $request, CreateCharacterAction $action): JsonResponse
    {
        $ruleset = Ruleset::query()
            ->where('name', RulesetSeeder::NAME)
            ->where('version', RulesetSeeder::VERSION)
            ->firstOrFail();

        $character = $action->execute($request->user(), $ruleset, $request->validated());

        return response()->json([
            'message' => 'Personnage créé avec succès',
            'character' => $character->load([
                'detail',
                'origin.specie',
                'origin.background',
                'classes.classDefinition',
                'classes.subclass',
                'abilities.ability',
                'skillProficiencies.skill',
                'languages.language',
                'tools.tool',
                'features.feature',
                'feats.feat',
                'spells.spell',
                'inventory.item',
            ]),
        ], 201);
    }

    /**
     * Supprime un personnage appartenant à l'utilisateur authentifié.
     *
     * Les données rattachées (origine, classes, niveaux, caractéristiques, inventaire, etc.)
     * sont supprimées par cascade au niveau de la base de données.
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, Character $character)
    {
        abort_unless($character->user_id === $request->user()->id, 404, 'Ressource non trouvée.');

        $character->delete();

        return response()->json(['message' => 'Personnage supprimé avec succès']);
    }
}
