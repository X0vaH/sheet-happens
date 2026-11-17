<?php

namespace App\Http\Resources;

use App\Models\ruleset\Ruleset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Agrège tout le catalogue nécessaire pour l'assistant de création de personnage niveau 1.
 *
 * @mixin Ruleset
 */
class RulesetCharacterOptionsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ruleset' => [
                'id' => $this->id,
                'name' => $this->name,
                'version' => $this->version,
                'edition' => $this->edition,
            ],
            'abilities' => AbilityResource::collection($this->whenLoaded('abilities')),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'languages' => LanguageResource::collection($this->whenLoaded('languages')),
            'alignments' => AlignmentResource::collection($this->whenLoaded('alignments')),
            'tools' => ToolResource::collection($this->whenLoaded('tools')),
            'species' => SpeciesResource::collection($this->whenLoaded('species')),
            'backgrounds' => BackgroundResource::collection($this->whenLoaded('backgrounds')),
            'classes' => CharacterClassOptionResource::collection($this->whenLoaded('classes')),
            'feats' => FeatResource::collection($this->whenLoaded('feats')),
            'items' => ItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
