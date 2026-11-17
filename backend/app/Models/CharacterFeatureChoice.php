<?php

namespace App\Models;

use Database\Factories\CharacterFeatureChoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_feature_id', 'feature_option_id'])]
class CharacterFeatureChoice extends Model
{
    /** @use HasFactory<CharacterFeatureChoiceFactory> */
    use HasFactory;

    public function characterFeature(): BelongsTo
    {
        return $this->belongsTo(CharacterFeature::class);
    }

    public function featureOption(): BelongsTo
    {
        return $this->belongsTo(FeatureOption::class);
    }
}
