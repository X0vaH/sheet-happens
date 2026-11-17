<?php

namespace App\Http\Resources;

use App\Models\ruleset\Armor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Armor */
class ArmorResource extends JsonResource
{
    /**
     * `dexterity_bonus_limit` : null = aucun plafond, 0 = aucun bonus de DEX, sinon plafond du bonus.
     * Pour un bouclier, `armor_class` est un bonus de CA et non une CA de base.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'armor_category' => $this->armor_category,
            'armor_class' => $this->armor_class,
            'dexterity_bonus_limit' => $this->dexterity_bonus_limit,
        ];
    }
}
