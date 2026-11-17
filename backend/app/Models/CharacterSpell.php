<?php

namespace App\Models;

use App\Models\ruleset\Spell;
use Database\Factories\CharacterSpellFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'spell_id', 'acquired_at_character_level'])]
class CharacterSpell extends Model
{
    /** @use HasFactory<CharacterSpellFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function spell(): BelongsTo
    {
        return $this->belongsTo(Spell::class);
    }
}
