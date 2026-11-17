<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Level;
use App\Models\ruleset\SubclassLevel;
use App\Support\AbilityScore;
use Illuminate\Validation\ValidationException;

class LevelUpOptions
{
    public const ABILITY_SCORE_IMPROVEMENT = 'Ability Score Improvement';

    public function forCharacter(Character $character): array
    {
        $current = (int) ($character->level?->character_level ?? 1);
        if ($current >= Level::MAX_LEVEL) {
            throw ValidationException::withMessages(['target_level' => 'Le niveau maximum est atteint.']);
        }
        $next = $current + 1;
        $primary = $character->classes()->with('classDefinition')->firstOrFail();
        $level = ClassLevel::where('class_id', $primary->class_id)
            ->whereHas('level', fn ($q) => $q->where('level_number', $next))
            ->with('features.options')->firstOrFail();
        if ($level->progression === null) {
            throw ValidationException::withMessages(['catalog' => 'Le catalogue de progression doit être initialisé avec les seeders.']);
        }
        $progression = $level->progression;
        $character->loadMissing('abilityIncreases');
        $abilities = $character->abilities()->with('ability')->get()->map(fn ($a) => [
            'id' => $a->ability_id, 'name' => $a->ability->name,
            'abbreviation' => $a->ability->abbreviation,
            'score' => (int) $a->score + (int) $character->abilityIncreases->where('ability_id', $a->ability_id)->sum('increase_amount'),
        ]);
        $subclasses = $primary->subclass_id ? collect() : $primary->classDefinition->subclasses()
            ->whereHas('classLevel.level', fn ($q) => $q->where('level_number', '<=', $next))->get();
        if ($subclasses->count() > 1) {
            throw ValidationException::withMessages(['catalog' => 'Une seule sous-classe doit être configurée par classe.']);
        }
        $subclassId = $primary->subclass_id ?? $subclasses->first()?->id;
        $subclassLevels = $subclassId ? SubclassLevel::where('subclass_id', $subclassId)
            ->whereHas('level', fn ($q) => $q->where('level_number', '<=', $next))
            ->with(['features.options', 'spells'])->get() : collect();
        $features = $level->features->merge($subclassLevels->flatMap->features)
            ->unique('id')->whereNotIn('id', $character->features()->pluck('feature_id'))->values();
        $knownSpells = $character->spells()->pluck('spell_id');
        $spells = $primary->classDefinition->spells()
            ->where('spell_level', '<=', count($progression['slots']))
            ->whereNotIn('spells.id', $knownSpells)->get();
        // Le clerc a accès à toute sa liste : seuls ses nouveaux tours de magie sont choisis.
        $grantedSpells = $subclassLevels->flatMap->spells;
        if ($primary->classDefinition->name === 'Clerc') {
            $grantedSpells = $grantedSpells->merge($spells->where('spell_level', '>', 0));
            $spells = $spells->where('spell_level', 0);
        }
        $grantedSpells = $grantedSpells->unique('id')->whereNotIn('id', $knownSpells)->values();
        $spells = $spells->whereNotIn('id', $grantedSpells->pluck('id'))->values();
        $constitution = $abilities->firstWhere('abbreviation', 'CON');
        $fixed = intdiv((int) $primary->classDefinition->hit_die, 2) + 1;

        return [
            'character' => ['id' => $character->id, 'name' => $character->name, 'current_level' => $current],
            'next_level' => $next,
            'hit_die' => (int) $primary->classDefinition->hit_die,
            'hit_points' => ['fixed' => $fixed, 'gain' => max(1, $fixed + AbilityScore::modifier($constitution['score'] ?? 10))],
            'progression' => $progression,
            'granted_spells' => $grantedSpells->toArray(),
            'available_options' => [
                'abilities' => $abilities->values()->all(),
                'subclasses' => $subclasses->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'description' => $s->description,
                    'features' => $subclassLevels->flatMap->features->unique('id')->values()->toArray(),
                ])->values()->all(),
                'features' => $features->toArray(),
                'feats' => $next === 4 ? Feat::where('ruleset_id', $character->ruleset_id)
                    ->where('category', 'general')
                    ->whereIn('name', [self::ABILITY_SCORE_IMPROVEMENT, ...array_keys(LevelUpFeatCatalog::FEATS)])
                    ->where(fn ($query) => $query->where('name', self::ABILITY_SCORE_IMPROVEMENT)
                        ->orWhereNotIn('id', $character->feats()->pluck('feat_id')))
                    ->get()->sortBy(fn ($feat) => $feat->name !== self::ABILITY_SCORE_IMPROVEMENT)->values()
                    ->map(fn ($feat) => [...$feat->toArray(), 'ability_increase_count' => $feat->name === self::ABILITY_SCORE_IMPROVEMENT ? 2 : 0])->all() : [],
                'spells' => $spells->toArray(),
                'ability_increase_count' => $progression['ability_points'],
            ],
        ];
    }
}
