<?php

namespace App\Models;

use Database\Factories\CharacterLevelClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_class_id', 'character_level_id', 'resulting_class_level'])]
class CharacterLevelClass extends Model
{
    /** @use HasFactory<CharacterLevelClassFactory> */
    use HasFactory;

    public function characterClass(): BelongsTo
    {
        return $this->belongsTo(CharacterClass::class);
    }

    public function characterLevel(): BelongsTo
    {
        return $this->belongsTo(CharacterLevel::class);
    }
}
