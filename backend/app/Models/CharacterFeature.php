<?php

namespace App\Models;

use App\Models\ruleset\Feature;
use Database\Factories\CharacterFeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['character_id', 'feature_id', 'acquired_at_character_level'])]
class CharacterFeature extends Model
{
    /** @use HasFactory<CharacterFeatureFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(CharacterFeatureChoice::class);
    }
}
