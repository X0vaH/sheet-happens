<?php

namespace App\Models;

use App\Models\ruleset\Item;
use Database\Factories\CharacterInventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'item_id', 'quantity'])]
class CharacterInventory extends Model
{
    /** @use HasFactory<CharacterInventoryFactory> */
    use HasFactory;

    protected $table = 'character_inventory';

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
