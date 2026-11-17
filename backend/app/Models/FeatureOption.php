<?php

namespace App\Models;

use App\Models\ruleset\Feature;
use Database\Factories\FeatureOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['feature_id', 'name', 'description', 'tool_id'])]
class FeatureOption extends Model
{
    /** @use HasFactory<FeatureOptionFactory> */
    use HasFactory;

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function featureChoices(): HasMany
    {
        return $this->hasMany(CharacterFeatureChoice::class);
    }
}
