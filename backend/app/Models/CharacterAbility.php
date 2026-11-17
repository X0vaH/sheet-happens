<?php

namespace App\Models;

use App\Models\ruleset\Ability;
use Database\Factories\CharacterAbilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'ability_id', 'score'])]
class CharacterAbility extends Model
{
    /** @use HasFactory<CharacterAbilityFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function ability(): BelongsTo
    {
        return $this->belongsTo(Ability::class);
    }
}
