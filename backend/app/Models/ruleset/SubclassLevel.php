<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\SubclassLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['subclass_id', 'level_id'])]
class SubclassLevel extends Model
{
    /** @use HasFactory<SubclassLevelFactory> */
    use HasFactory;

    /**
     * Retourne la sous-classe de ce niveau de sous-classe.
     *
     * @return BelongsTo<Subclass, $this>
     */
    public function subclass(): BelongsTo
    {
        return $this->belongsTo(Subclass::class);
    }

    /**
     * Retourne le niveau de ce niveau de sous-classe.
     *
     * @return BelongsTo<Level, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Retourne les aptitudes de ce niveau de sous-classe, via la table pivot `subclass_features`.
     *
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'subclass_features');
    }

    /**
     * Retourne les sorts de ce niveau de sous-classe, via la table pivot `subclass_level_spells`.
     *
     * @return BelongsToMany<Spell, $this>
     */
    public function spells(): BelongsToMany
    {
        return $this->belongsToMany(Spell::class, 'subclass_level_spells');
    }
}
