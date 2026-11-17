<?php

namespace App\Models\ruleset;

use Database\Factories\ruleset\RuleSourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ruleset_id', 'name', 'source_type', 'publication_date', 'description'])]
class RuleSource extends Model
{
    /** @use HasFactory<RuleSourceFactory> */
    use HasFactory;

    /**
     * Retourne les attributs à convertir (casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
        ];
    }

    /**
     * Retourne le ruleset de cette source de règles.
     *
     * @return BelongsTo<Ruleset, $this>
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }
}
