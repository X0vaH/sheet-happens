<?php

namespace App\Models;

use App\Models\ruleset\Tool;
use Database\Factories\CharacterToolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'tool_id', 'proficiency_level'])]
class CharacterTool extends Model
{
    /** @use HasFactory<CharacterToolFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}
