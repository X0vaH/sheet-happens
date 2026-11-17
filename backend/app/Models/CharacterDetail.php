<?php

namespace App\Models;

use Database\Factories\CharacterDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'ideals', 'bonds', 'flaws', 'backstory', 'personality_traits'])]
class CharacterDetail extends Model
{
    /** @use HasFactory<CharacterDetailFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
