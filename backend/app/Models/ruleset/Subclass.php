<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\SubclassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['class_level_id', 'name', 'description'])]
class Subclass extends Model
{
    /** @use HasFactory<SubclassFactory> */
    use HasFactory;

    /**
     * Retourne le niveau de classe de cette sous-classe.
     *
     * @return BelongsTo<ClassLevel, $this>
     */
    public function classLevel(): BelongsTo
    {
        return $this->belongsTo(ClassLevel::class);
    }

    /**
     * Retourne les niveaux de sous-classe de cette sous-classe.
     *
     * @return HasMany<SubclassLevel, $this>
     */
    public function subclassLevels(): HasMany
    {
        return $this->hasMany(SubclassLevel::class);
    }

    /**
     * Retourne les sorts de cette sous-classe, via la table pivot `subclass_spells`.
     *
     * @return BelongsToMany<Spell, $this>
     */
    public function spells(): BelongsToMany
    {
        return $this->belongsToMany(Spell::class, 'subclass_spells');
    }
}
