<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\ArmorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['item_id', 'armor_category', 'armor_class', 'dexterity_bonus_limit', 'strength_requirement', 'stealth_disadvantage'])]
class Armor extends Model
{
    /** @use HasFactory<ArmorFactory> */
    use HasFactory;

    protected $table = 'armor';

    /**
     * Retourne les attributs à convertir (casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'armor_class' => 'integer',
            'dexterity_bonus_limit' => 'integer',
            'strength_requirement' => 'integer',
            'stealth_disadvantage' => 'boolean',
        ];
    }

    /**
     * Retourne l'objet de cette armure.
     *
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
