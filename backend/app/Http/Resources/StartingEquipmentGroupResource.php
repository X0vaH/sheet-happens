<?php

namespace App\Http\Resources;

use App\Models\ruleset\StartingEquipmentGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Un groupe avec une seule option n'est pas un vrai choix : c'est de l'équipement
 * accordé automatiquement (voir App\Models\ruleset\StartingEquipmentGroup).
 *
 * @mixin StartingEquipmentGroup
 */
class StartingEquipmentGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'options' => StartingEquipmentOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
