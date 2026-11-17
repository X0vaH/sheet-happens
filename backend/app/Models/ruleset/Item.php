<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['ruleset_id', 'name', 'item_type', 'tool_id', 'description', 'weight', 'value'])]
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cet objet.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne l'outil de cet objet.
     *
     * @return BelongsTo<Tool, $this>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /**
     * Retourne l'arme de cet objet.
     *
     * @return HasOne<Weapon, $this>
     */
    public function weapon(): HasOne
    {
        return $this->hasOne(Weapon::class);
    }

    /**
     * Retourne l'armure de cet objet.
     *
     * @return HasOne<Armor, $this>
     */
    public function armor(): HasOne
    {
        return $this->hasOne(Armor::class);
    }
}
