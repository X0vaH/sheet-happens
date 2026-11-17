<?php

namespace App\Models;

use App\Models\ruleset\Background;
use App\Models\ruleset\Species;
use Database\Factories\CharacterOriginFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'specie_id', 'background_id'])]
class CharacterOrigin extends Model
{
    /** @use HasFactory<CharacterOriginFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function specie(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'specie_id');
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(Background::class);
    }
}
