<?php

namespace App\Models;

use App\Models\ruleset\Ruleset;
use Database\Factories\CharacterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'ruleset_id', 'name', 'current_health_points', 'max_health_points', 'alignment', 'notes', 'order_index'])]
class Character extends Model
{
    /** @use HasFactory<CharacterFactory> */
    use HasFactory;

    /** Nombre maximal de personnages sauvegardés par utilisateur. */
    public const MAX_PER_USER = 5;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(CharacterDetail::class);
    }

    public function origin(): HasOne
    {
        return $this->hasOne(CharacterOrigin::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(CharacterInventory::class);
    }

    public function abilityIncreases(): HasMany
    {
        return $this->hasMany(CharacterAbilityIncrease::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(CharacterClass::class);
    }

    public function level(): HasOne
    {
        return $this->hasOne(CharacterLevel::class)->latestOfMany(['character_level', 'id']);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(CharacterLevel::class);
    }

    public function abilities(): HasMany
    {
        return $this->hasMany(CharacterAbility::class);
    }

    public function skillProficiencies(): HasMany
    {
        return $this->hasMany(CharacterSkillProficiency::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CharacterLanguage::class);
    }

    public function tools(): HasMany
    {
        return $this->hasMany(CharacterTool::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(CharacterFeature::class);
    }

    public function feats(): HasMany
    {
        return $this->hasMany(CharacterFeat::class);
    }

    public function spells(): HasMany
    {
        return $this->hasMany(CharacterSpell::class);
    }

    public function spellSlots(): HasMany
    {
        return $this->hasMany(CharacterSpellSlot::class);
    }
}
