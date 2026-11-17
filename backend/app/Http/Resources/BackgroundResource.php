<?php

namespace App\Http\Resources;

use App\Models\ruleset\Background;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Background */
class BackgroundResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'language_choice_count' => $this->language_choice_count,
            'abilities' => AbilityResource::collection($this->whenLoaded('abilities')),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'tools' => ToolResource::collection($this->whenLoaded('tools')),
            'feats' => FeatResource::collection($this->whenLoaded('feats')),
        ];
    }
}
