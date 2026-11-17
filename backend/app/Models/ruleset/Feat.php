<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\FeatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['ruleset_id', 'name', 'category', 'description'])]
class Feat extends Model
{
    /** @use HasFactory<FeatFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de ce don.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les historiques de ce don, via la table pivot `background_feats`.
     *
     * @return BelongsToMany<Background, $this>
     */
    public function backgrounds(): BelongsToMany
    {
        return $this->belongsToMany(Background::class, 'background_feats');
    }
}
