<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\ClassProficiencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une maîtrise d'armure ou d'arme accordée par une classe (ex : « Toutes les armures »,
 * « Armes courantes et armes de guerre »).
 */
#[Fillable(['class_id', 'type', 'label'])]
class ClassProficiency extends Model
{
    /** @use HasFactory<ClassProficiencyFactory> */
    use HasFactory;

    /**
     * Retourne la classe de cette maîtrise.
     *
     * @return BelongsTo<CharacterClass, $this>
     */
    public function characterClass(): BelongsTo
    {
        return $this->belongsTo(CharacterClass::class, 'class_id');
    }
}
