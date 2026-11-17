<?php

namespace App\Http\Resources;

use App\Models\ruleset\Species;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Species */
class SpeciesResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'size_category' => $this->size_category,
            'base_speed' => $this->base_speed,
            'description' => $this->description,
            'features' => FeatureResource::collection($this->whenLoaded('features')),
            'languages' => LanguageResource::collection($this->whenLoaded('languages')),
        ];
    }
}
