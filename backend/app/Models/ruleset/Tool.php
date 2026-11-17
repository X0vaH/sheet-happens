<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\ToolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ruleset_id', 'name', 'tool_category'])]
class Tool extends Model
{
    /** @use HasFactory<ToolFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cet outil.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Retourne les historiques de cet outil, via la table pivot `background_tools`.
     *
     * @return BelongsToMany<Background, $this>
     */
    public function backgrounds(): BelongsToMany
    {
        return $this->belongsToMany(Background::class, 'background_tools');
    }

    /**
     * Retourne les objets de cet outil.
     *
     * @return HasMany<Item, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
