<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\BackgroundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['ruleset_id', 'name', 'description', 'language_choice_count'])]
class Background extends Model
{
    /** @use HasFactory<BackgroundFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cet historique.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les caractéristiques de cet historique, via la table pivot `background_abilities`.
     *
     * @return BelongsToMany<Ability, $this>
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'background_abilities');
    }

    /**
     * Retourne les compétences de cet historique, via la table pivot `background_skills`.
     *
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'background_skills');
    }

    /**
     * Retourne les outils de cet historique, via la table pivot `background_tools`.
     *
     * @return BelongsToMany<Tool, $this>
     */
    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'background_tools');
    }

    /**
     * Retourne les dons de cet historique, via la table pivot `background_feats`.
     *
     * @return BelongsToMany<Feat, $this>
     */
    public function feats(): BelongsToMany
    {
        return $this->belongsToMany(Feat::class, 'background_feats');
    }
}
