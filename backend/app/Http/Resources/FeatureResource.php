<?php

namespace App\Http\Resources;

use App\Models\ruleset\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Feature */
class FeatureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'feature_type' => $this->feature_type,
            'description' => $this->description,
            'options' => FeatureOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
