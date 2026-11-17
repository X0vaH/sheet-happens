<?php

namespace App\Http\Resources;

use App\Models\ruleset\ClassLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Représente le contenu débloqué à un niveau donné d'une classe (niveau 1 uniquement, pour la création de personnage).
 *
 * @mixin ClassLevel
 */
class ClassLevelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'features' => FeatureResource::collection($this->whenLoaded('features')),
            'spells' => SpellResource::collection($this->whenLoaded('spells')),
            'subclasses' => SubclassResource::collection($this->whenLoaded('subclasses')),
        ];
    }
}
