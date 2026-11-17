<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\WeaponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'item_id',
    'weapon_category',
    'damage_dice',
    'damage_type',
    'versatile_damage_dice',
    'range_normal',
    'range_long',
    'properties',
    'mastery_property',
])]
class Weapon extends Model
{
    /** @use HasFactory<WeaponFactory> */
    use HasFactory;

    /**
     * Retourne les attributs à convertir (casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * Retourne l'objet de cette arme.
     *
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
