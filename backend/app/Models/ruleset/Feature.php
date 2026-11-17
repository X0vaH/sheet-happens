<?php

namespace App\Models\ruleset;

use App\Models\FeatureOption;
use Database\Factories\ruleset\FeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ruleset_id', 'name', 'feature_type', 'description', 'choice_count'])]
class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cette aptitude.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les options de choix disponibles pour cette aptitude (ex: domaines divins d'un Clerc).
     *
     * @return HasMany<FeatureOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(FeatureOption::class);
    }

    /**
     * Retourne les espèces de cette aptitude, via la table pivot `species_features`.
     *
     * @return BelongsToMany<Species, $this>
     */
    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'species_features', 'feature_id', 'specie_id');
    }

    /**
     * Retourne les niveaux de classe de cette aptitude, via la table pivot `class_level_features`.
     *
     * @return BelongsToMany<ClassLevel, $this>
     */
    public function classLevels(): BelongsToMany
    {
        return $this->belongsToMany(ClassLevel::class, 'class_level_features');
    }

    /**
     * Retourne les niveaux de sous-classe de cette aptitude, via la table pivot `subclass_features`.
     *
     * @return BelongsToMany<SubclassLevel, $this>
     */
    public function subclassLevels(): BelongsToMany
    {
        return $this->belongsToMany(SubclassLevel::class, 'subclass_features');
    }
}
