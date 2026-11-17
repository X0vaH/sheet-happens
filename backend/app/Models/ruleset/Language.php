<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\LanguageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ruleset_id', 'name', 'description'])]
class Language extends Model
{
    /** @use HasFactory<LanguageFactory> */
    use HasFactory;

    /**
     * Retourne le ruleset de cette langue.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }
}
