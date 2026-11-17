<?php

namespace App\Models;

use Database\Factories\CharacterLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['character_id', 'character_level', 'experience_points'])]
class CharacterLevel extends Model
{
    /** @use HasFactory<CharacterLevelFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function levelClasses(): HasMany
    {
        return $this->hasMany(CharacterLevelClass::class);
    }
}
