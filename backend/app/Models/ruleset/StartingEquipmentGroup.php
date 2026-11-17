<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\StartingEquipmentGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un choix d'équipement de départ pour une classe (ex : "armure", "arme principale").
 * Un groupe avec une seule option n'est pas un vrai choix : c'est de l'équipement
 * accordé automatiquement, modélisé pareil pour ne pas dupliquer un mécanisme séparé.
 */
#[Fillable(['class_id', 'label'])]
class StartingEquipmentGroup extends Model
{
    /** @use HasFactory<StartingEquipmentGroupFactory> */
    use HasFactory;

    /**
     * Retourne la classe de ce groupe.
     *
     * @return BelongsTo<CharacterClass, $this>
     */
    public function characterClass(): BelongsTo
    {
        return $this->belongsTo(CharacterClass::class, 'class_id');
    }

    /**
     * Retourne les options de ce groupe.
     *
     * @return HasMany<StartingEquipmentOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(StartingEquipmentOption::class);
    }
}
