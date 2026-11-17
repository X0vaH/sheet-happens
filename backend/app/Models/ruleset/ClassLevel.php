<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\ClassLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['class_id', 'level_id', 'progression'])]
class ClassLevel extends Model
{
    /** @use HasFactory<ClassLevelFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['progression' => 'array'];
    }

    /**
     * Retourne la classe de ce niveau de classe.
     *
     * @return BelongsTo<CharacterClass, $this>
     */
    public function characterClass(): BelongsTo
    {
        return $this->belongsTo(CharacterClass::class, 'class_id');
    }

    /**
     * Retourne le niveau de ce niveau de classe.
     *
     * @return BelongsTo<Level, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Retourne les sous-classes de ce niveau de classe.
     *
     * @return HasMany<Subclass, $this>
     */
    public function subclasses(): HasMany
    {
        return $this->hasMany(Subclass::class);
    }

    /**
     * Retourne les aptitudes de ce niveau de classe, via la table pivot `class_level_features`.
     *
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'class_level_features');
    }

    /**
     * Retourne les sorts de ce niveau de classe, via la table pivot `class_level_spells`.
     *
     * @return BelongsToMany<Spell, $this>
     */
    public function spells(): BelongsToMany
    {
        return $this->belongsToMany(Spell::class, 'class_level_spells');
    }
}
