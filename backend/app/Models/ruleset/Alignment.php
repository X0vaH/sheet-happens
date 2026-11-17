<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\AlignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ruleset_id', 'name', 'description'])]
class Alignment extends Model
{
    /** @use HasFactory<AlignmentFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cet alignement.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }
}
