<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\AbilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ruleset_id', 'name', 'abbreviation'])]
class Ability extends Model
{
    /** @use HasFactory<AbilityFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cette caractéristique.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les compétences de cette caractéristique.
     *
     * @return HasMany<Skill, $this>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Retourne les historiques de cette caractéristique, via la table pivot `background_abilities`.
     *
     * @return BelongsToMany<Background, $this>
     */
    public function backgrounds(): BelongsToMany
    {
        return $this->belongsToMany(Background::class, 'background_abilities');
    }

    /**
     * Retourne les classes de cette caractéristique, via la table pivot `class_saving_throws`.
     *
     * @return BelongsToMany<CharacterClass, $this>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(CharacterClass::class, 'class_saving_throws', 'ability_id', 'class_id');
    }
}
