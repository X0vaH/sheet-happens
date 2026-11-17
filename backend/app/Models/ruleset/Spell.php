<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\SpellFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'ruleset_id',
    'name',
    'spell_level',
    'school',
    'casting_time',
    'range',
    'duration',
    'concentration',
    'ritual',
    'verbal',
    'somatic',
    'material',
    'material_description',
    'description',
])]
class Spell extends Model
{
    /** @use HasFactory<SpellFactory> */
    use HasFactory;

    /**
     * Retourne les attributs à convertir (casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'concentration' => 'boolean',
            'ritual' => 'boolean',
            'verbal' => 'boolean',
            'somatic' => 'boolean',
            'material' => 'boolean',
        ];
    }

    /**
     * Retourne le ruleset de ce sort.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les classes de ce sort, via la table pivot `class_spells`.
     *
     * @return BelongsToMany<CharacterClass, $this>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(CharacterClass::class, 'class_spells', 'spell_id', 'class_id');
    }

    /**
     * Retourne les niveaux de classe de ce sort, via la table pivot `class_level_spells`.
     *
     * @return BelongsToMany<ClassLevel, $this>
     */
    public function classLevels(): BelongsToMany
    {
        return $this->belongsToMany(ClassLevel::class, 'class_level_spells');
    }

    /**
     * Retourne les sous-classes de ce sort, via la table pivot `subclass_spells`.
     *
     * @return BelongsToMany<Subclass, $this>
     */
    public function subclasses(): BelongsToMany
    {
        return $this->belongsToMany(Subclass::class, 'subclass_spells');
    }

    /**
     * Retourne les niveaux de sous-classe de ce sort, via la table pivot `subclass_level_spells`.
     *
     * @return BelongsToMany<SubclassLevel, $this>
     */
    public function subclassLevels(): BelongsToMany
    {
        return $this->belongsToMany(SubclassLevel::class, 'subclass_level_spells');
    }
}
