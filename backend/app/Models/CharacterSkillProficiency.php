<?php

namespace App\Models;

use App\Models\ruleset\Skill;
use Database\Factories\CharacterSkillProficiencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['character_id', 'skill_id', 'proficiency_level'])]
class CharacterSkillProficiency extends Model
{
    /** @use HasFactory<CharacterSkillProficiencyFactory> */
    use HasFactory;

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
