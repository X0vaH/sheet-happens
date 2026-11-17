<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\LevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ruleset_id', 'level_number', 'proficiency_bonus'])]
class Level extends Model
{
    /** @use HasFactory<LevelFactory> */
    use HasFactory;

    /** Niveau maximum qu'un personnage peut atteindre dans l'application. */
    public const MAX_LEVEL = 5;

    /**
     * Retourne le ruleset de ce niveau.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les niveaux de classe de ce niveau.
     *
     * @return HasMany<ClassLevel, $this>
     */
    public function classLevels(): HasMany
    {
        return $this->hasMany(ClassLevel::class);
    }

    /**
     * Retourne les niveaux de sous-classe de ce niveau.
     *
     * @return HasMany<SubclassLevel, $this>
     */
    public function subclassLevels(): HasMany
    {
        return $this->hasMany(SubclassLevel::class);
    }
}
