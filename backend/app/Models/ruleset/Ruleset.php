<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\RulesetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'version', 'edition', 'description', 'status', 'published_at', 'retired_at'])]
class Ruleset extends Model
{
    /** @use HasFactory<RulesetFactory> */
    use HasFactory;

    /**
     * Retourne les attributs à convertir (casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }

    /**
     * Retourne les sources de règles de ce ruleset.
     *
     * @return HasMany<RuleSource, $this>
     */
    public function ruleSources(): HasMany
    {
        return $this->hasMany(RuleSource::class);
    }

    /**
     * Retourne les caractéristiques de ce ruleset.
     *
     * @return HasMany<Ability, $this>
     */
    public function abilities(): HasMany
    {
        return $this->hasMany(Ability::class);
    }

    /**
     * Retourne les compétences de ce ruleset.
     *
     * @return HasMany<Skill, $this>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Retourne les langues de ce ruleset.
     *
     * @return HasMany<Language, $this>
     */
    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    /**
     * Retourne les alignements de ce ruleset.
     *
     * @return HasMany<Alignment, $this>
     */
    public function alignments(): HasMany
    {
        return $this->hasMany(Alignment::class);
    }

    /**
     * Retourne les outils de ce ruleset.
     *
     * @return HasMany<Tool, $this>
     */
    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    /**
     * Retourne les espèces de ce ruleset.
     *
     * @return HasMany<Species, $this>
     */
    public function species(): HasMany
    {
        return $this->hasMany(Species::class);
    }

    /**
     * Retourne les historiques de ce ruleset.
     *
     * @return HasMany<Background, $this>
     */
    public function backgrounds(): HasMany
    {
        return $this->hasMany(Background::class);
    }

    /**
     * Retourne les classes de ce ruleset.
     *
     * @return HasMany<CharacterClass, $this>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(CharacterClass::class, 'ruleset_id');
    }

    /**
     * Retourne les aptitudes de ce ruleset.
     *
     * @return HasMany<Feature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    /**
     * Retourne les dons de ce ruleset.
     *
     * @return HasMany<Feat, $this>
     */
    public function feats(): HasMany
    {
        return $this->hasMany(Feat::class);
    }

    /**
     * Retourne les niveaux de ce ruleset.
     *
     * @return HasMany<Level, $this>
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Retourne les sorts de ce ruleset.
     *
     * @return HasMany<Spell, $this>
     */
    public function spells(): HasMany
    {
        return $this->hasMany(Spell::class);
    }

    /**
     * Retourne les objets de ce ruleset.
     *
     * @return HasMany<Item, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
