<?php

namespace App\Models;

use Database\Factories\CharacterSpellSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'spell_level', 'max_slots', 'expended_slots'])]
class CharacterSpellSlot extends Model
{
    /** @use HasFactory<CharacterSpellSlotFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
