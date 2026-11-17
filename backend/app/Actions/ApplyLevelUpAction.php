<?php

namespace App\Actions;

use App\Models\Character;
use App\Models\CharacterLevelClass;
use App\Services\LevelUpOptions;
use App\Support\AbilityScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplyLevelUpAction
{
    public function execute(Character $character, array $data): Character
    {
        return DB::transaction(function () use ($character, $data): Character {
            $character = Character::whereKey($character->id)->lockForUpdate()->firstOrFail();
            $options = app(LevelUpOptions::class)->forCharacter($character);
            $available = $options['available_options'];
            $progression = $options['progression'];
            $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
            if ((int) $data['target_level'] !== $options['next_level']) {
                $fail('target_level', 'Rechargez le personnage avant de passer au niveau suivant.');
            }
            $featId = $data['feat_id'] ?? null;
            $feats = collect($available['feats']);
            if (($options['next_level'] === 4 && ! $feats->contains('id', $featId)) || ($options['next_level'] !== 4 && $featId !== null)) {
                $fail('feat_id', 'Choisissez un don disponible pour ce niveau.');
            }
            $choices = collect($data['feature_choices']);
            $requiredFeatures = collect($available['features'])->filter(fn ($feature) => ($feature['choice_count'] ?? 0) > 0)->keyBy('id');
            if ($choices->count() !== $requiredFeatures->count()) {
                $fail('feature_choices', 'Tous les choix de capacités requis doivent être fournis.');
            }
            foreach ($choices as $choice) {
                $feature = $requiredFeatures->get($choice['feature_id']);
                if (! $feature || count($choice['option_ids']) !== $feature['choice_count'] || count(array_diff($choice['option_ids'], array_column($feature['options'], 'id'))) > 0) {
                    $fail('feature_choices', 'Choisissez le bon nombre d’options appartenant à chaque capacité.');
                }
            }
            $increases = collect($data['ability_increases']);
            $abilityPoints = $options['next_level'] === 4
                ? $feats->firstWhere('id', $featId)['ability_increase_count']
                : $progression['ability_points'];
            if ((int) $increases->sum('increase_amount') !== $abilityPoints) {
                $fail('ability_increases', 'Le nombre de points de caractéristiques est incorrect.');
            }
            $abilities = collect($available['abilities'])->keyBy('id');
            foreach ($increases as $increase) {
                $ability = $abilities->get($increase['ability_id']);
                if (! $ability || $ability['score'] + $increase['increase_amount'] > 20) {
                    $fail('ability_increases', 'Caractéristique invalide ou score supérieur à 20.');
                }
            }
            $subclasses = collect($available['subclasses']);
            $subclassId = $subclasses->first()['id'] ?? null;
            if (isset($data['subclass_id']) && $data['subclass_id'] !== $subclassId) {
                $fail('subclass_id', 'La sous-classe est attribuée automatiquement pour ce niveau.');
            }
            $spells = collect($available['spells'])->whereIn('id', $data['spell_ids']);
            if ($spells->count() !== count($data['spell_ids']) || $spells->where('spell_level', 0)->count() !== $progression['cantrip_count'] || $spells->where('spell_level', '>', 0)->count() !== $progression['spell_count']) {
                $fail('spell_ids', 'Choisissez le nombre demandé de nouveaux sorts accessibles.');
            }
            $baseHp = intdiv($options['hit_die'], 2) + 1;
            $constitution = $abilities->firstWhere('abbreviation', 'CON');
            $oldModifier = AbilityScore::modifier($constitution['score'] ?? 10);
            $increase = $constitution ? $increases->where('ability_id', $constitution['id'])->sum('increase_amount') : 0;
            $newModifier = AbilityScore::modifier(($constitution['score'] ?? 10) + $increase);
            $gain = max(1, $baseHp + $newModifier) + max(1, $options['hit_die'] + $newModifier) - max(1, $options['hit_die'] + $oldModifier)
                + ($options['next_level'] - 2) * (max(1, $baseHp + $newModifier) - max(1, $baseHp + $oldModifier));
            $character->increment('max_health_points', $gain);
            $character->increment('current_health_points', $gain);
            $level = $character->levels()->create(['character_level' => $options['next_level'], 'experience_points' => $character->level?->experience_points ?? 0]);
            $primary = $character->classes()->firstOrFail();
            CharacterLevelClass::create(['character_class_id' => $primary->id, 'character_level_id' => $level->id, 'resulting_class_level' => $options['next_level']]);
            foreach ($increases as $entry) {
                $character->abilityIncreases()->create([...$entry, 'acquired_at_character_level' => $options['next_level']]);
            }
            $features = collect($available['features']);
            if ($subclassId) {
                $primary->update(['subclass_id' => $subclassId]);
            }
            $toolRows = [];
            foreach ($features->unique('id') as $feature) {
                $acquired = $character->features()->create(['feature_id' => $feature['id'], 'acquired_at_character_level' => $options['next_level']]);
                $selectedOptions = collect($feature['options'])->keyBy('id');
                foreach (($choices->firstWhere('feature_id', $feature['id'])['option_ids'] ?? []) as $optionId) {
                    $acquired->choices()->create(['feature_option_id' => $optionId]);
                    $option = $selectedOptions->get($optionId);
                    if ($option['tool_id'] ?? null) {
                        $toolRows[$option['tool_id']] = ['character_id' => $character->id, 'tool_id' => $option['tool_id'], 'proficiency_level' => 'proficient'];
                    }
                }
            }
            if ($toolRows) {
                $character->tools()->upsert(array_values($toolRows), ['character_id', 'tool_id'], ['proficiency_level']);
            }
            if ($featId !== null) {
                $character->feats()->create(['feat_id' => $featId, 'acquired_at_character_level' => $options['next_level']]);
            }
            $acquiredAt = now();
            $spellRows = array_map(fn ($spellId) => ['character_id' => $character->id, 'spell_id' => $spellId, 'acquired_at_character_level' => $options['next_level'], 'created_at' => $acquiredAt, 'updated_at' => $acquiredAt], array_values(array_unique([...$data['spell_ids'], ...array_column($options['granted_spells'], 'id')])));
            if ($spellRows) {
                $character->spells()->insert($spellRows);
            }
            $slotRows = [];
            foreach ($progression['slots'] as $index => $count) {
                $slotRows[] = ['character_id' => $character->id, 'spell_level' => $index + 1, 'max_slots' => $count, 'expended_slots' => 0];
            }
            if ($slotRows) {
                $character->spellSlots()->upsert($slotRows, ['character_id', 'spell_level'], ['max_slots']);
            }

            return $character->refresh();
        });
    }
}
