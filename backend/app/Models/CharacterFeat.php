<?php

namespace App\Models;

use App\Models\ruleset\Feat;
use Database\Factories\CharacterFeatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'feat_id', 'acquired_at_character_level'])]
class CharacterFeat extends Model
{
    /** @use HasFactory<CharacterFeatFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function feat(): BelongsTo
    {
        return $this->belongsTo(Feat::class);
    }
}
