<?php

namespace App\Http\Controllers;

use App\Http\Resources\RulesetCharacterOptionsResource;
use App\Models\ruleset\Ruleset;
use Database\Seeders\ruleset\RulesetSeeder;
use Illuminate\Http\JsonResponse;

class RulesetController extends Controller
{
    /**
     * Retourne tout le catalogue nécessaire pour l'assistant de création de personnage niveau 1,
     * scopé au ruleset actif (identifié par nom + version, comme RulesetSeeder).
     */
    public function characterCreationOptions(): JsonResponse
    {
        $ruleset = Ruleset::query()
            ->where('name', RulesetSeeder::NAME)
            ->where('version', RulesetSeeder::VERSION)
            ->with([
                // Ordre stable et documenté : tri par id (FOR, DEX, CON, INT, SAG, CHA), sur lequel
                // le frontend indexe les caractéristiques.
                'abilities' => fn ($query) => $query->orderBy('id'),
                'skills',
                'languages',
                'alignments',
                'tools',
                'species.features.options',
                'species.languages',
                'backgrounds.abilities',
                'backgrounds.skills',
                'backgrounds.tools',
                'backgrounds.feats',
                'classes.savingThrows',
                'classes.spells',
                'classes.proficiencies',
                'classes.classLevels' => fn ($query) => $query
                    ->whereHas('level', fn ($level) => $level->where('level_number', 1))
                    ->with(['features.options', 'spells', 'subclasses']),
                'classes.startingEquipmentGroups.options.items',
                'feats',
                'items.armor',
            ])
            ->firstOrFail();

        return response()->json([
            'options' => new RulesetCharacterOptionsResource($ruleset),
        ]);
    }
}
