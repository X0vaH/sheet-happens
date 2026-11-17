<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['ruleset_id', 'name', 'ability_id'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cette compétence.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne la caractéristique de cette compétence.
     *
     * @return BelongsTo<Ability, $this>
     */
    public function ability(): BelongsTo
    {
        return $this->belongsTo(Ability::class);
    }

    /**
     * Retourne les historiques de cette compétence, via la table pivot `background_skills`.
     *
     * @return BelongsToMany<Background, $this>
     */
    public function backgrounds(): BelongsToMany
    {
        return $this->belongsToMany(Background::class, 'background_skills');
    }
}
