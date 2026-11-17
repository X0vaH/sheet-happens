<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\CharacterClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['ruleset_id', 'name', 'hit_die', 'description', 'skill_choice_count', 'cantrip_choice_count', 'spell_choice_count'])]
class CharacterClass extends Model
{
    /** @use HasFactory<CharacterClassFactory> */
    use HasFactory;

    protected $table = 'classes';

    /**
     * Retourne le ruleset de cette classe.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les caractéristiques de cette classe, via la table pivot `class_saving_throws`.
     *
     * @return BelongsToMany<Ability, $this>
     */
    public function savingThrows(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'class_saving_throws', 'class_id', 'ability_id');
    }

    /**
     * Retourne les niveaux de classe de cette classe.
     *
     * @return HasMany<ClassLevel, $this>
     */
    public function classLevels(): HasMany
    {
        return $this->hasMany(ClassLevel::class, 'class_id');
    }

    /**
     * Retourne les sous-classes de cette classe, en passant par les niveaux de classe.
     *
     * @return HasManyThrough<Subclass, ClassLevel, $this>
     */
    public function subclasses(): HasManyThrough
    {
        return $this->hasManyThrough(Subclass::class, ClassLevel::class, 'class_id', 'class_level_id');
    }

    /**
     * Retourne les sorts de cette classe, via la table pivot `class_spells`.
     *
     * @return BelongsToMany<Spell, $this>
     */
    public function spells(): BelongsToMany
    {
        return $this->belongsToMany(Spell::class, 'class_spells', 'class_id', 'spell_id');
    }

    /**
     * Retourne les groupes de choix d'équipement de départ de cette classe.
     *
     * @return HasMany<StartingEquipmentGroup, $this>
     */
    public function startingEquipmentGroups(): HasMany
    {
        return $this->hasMany(StartingEquipmentGroup::class, 'class_id');
    }

    /**
     * Retourne les maîtrises d'armures et d'armes de cette classe.
     *
     * @return HasMany<ClassProficiency, $this>
     */
    public function proficiencies(): HasMany
    {
        return $this->hasMany(ClassProficiency::class, 'class_id');
    }
}
