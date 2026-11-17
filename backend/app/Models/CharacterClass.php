<?php

namespace App\Models;

use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\Subclass;
use Database\Factories\CharacterClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['character_id', 'class_id', 'subclass_id', 'first_taken_at_character_level'])]
class CharacterClass extends Model
{
    /** @use HasFactory<CharacterClassFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function levelClasses(): HasMany
    {
        return $this->hasMany(CharacterLevelClass::class);
    }

    public function classDefinition(): BelongsTo
    {
        return $this->belongsTo(ClassDefinition::class, 'class_id');
    }

    public function subclass(): BelongsTo
    {
        return $this->belongsTo(Subclass::class);
    }
}
