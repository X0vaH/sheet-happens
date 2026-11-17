<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\SpeciesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['ruleset_id', 'name', 'size_category', 'base_speed', 'description'])]
class Species extends Model
{
    /** @use HasFactory<SpeciesFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cette espèce.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les aptitudes de cette espèce, via la table pivot `species_features`.
     *
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'species_features', 'specie_id', 'feature_id');
    }

    /**
     * Retourne les langues fixes accordées par cette espèce, via la table pivot `species_languages`.
     *
     * @return BelongsToMany<Language, $this>
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'species_languages', 'specie_id', 'language_id');
    }
}
