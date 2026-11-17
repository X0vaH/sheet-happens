<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\StartingEquipmentOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Une alternative au sein d'un groupe d'équipement de départ (ex : "Cotte de mailles" OU
 * "Armure de cuir + arc long"). Regroupe un ou plusieurs objets, chacun avec sa quantité.
 */
#[Fillable(['starting_equipment_group_id', 'label'])]
class StartingEquipmentOption extends Model
{
    /** @use HasFactory<StartingEquipmentOptionFactory> */
    use HasFactory;

    /**
     * Retourne le groupe de cette option.
     *
     * @return BelongsTo<StartingEquipmentGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(StartingEquipmentGroup::class, 'starting_equipment_group_id');
    }

    /**
     * Retourne les objets de cette option, avec leur quantité, via la table pivot
     * `starting_equipment_option_items`.
     *
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'starting_equipment_option_items')->withPivot('quantity');
    }
}
