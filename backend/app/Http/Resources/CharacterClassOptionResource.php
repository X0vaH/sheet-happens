<?php

namespace App\Http\Resources;

use App\Models\ruleset\CharacterClass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CharacterClass */
class CharacterClassOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hit_die' => $this->hit_die,
            'description' => $this->description,
            'skill_choice_count' => $this->skill_choice_count,
            'cantrip_choice_count' => $this->cantrip_choice_count,
            'spell_choice_count' => $this->spell_choice_count,
            'saving_throws' => AbilityResource::collection($this->whenLoaded('savingThrows')),
            'proficiencies' => $this->whenLoaded('proficiencies', fn () => $this->proficiencies->pluck('label')),
            // Liste complète des sorts accessibles à la classe (indépendamment du niveau) ;
            // level_one.spells (plus bas) ne contient que ceux déjà appris automatiquement.
            'spells' => SpellResource::collection($this->whenLoaded('spells')),
            // classLevels est filtré au niveau 1 uniquement par le contrôleur (portée "création niveau 1").
            'level_one' => $this->whenLoaded(
                'classLevels',
                fn () => $this->classLevels->isNotEmpty() ? new ClassLevelResource($this->classLevels->first()) : null,
            ),
            'starting_equipment_groups' => StartingEquipmentGroupResource::collection($this->whenLoaded('startingEquipmentGroups')),
        ];
    }
}
