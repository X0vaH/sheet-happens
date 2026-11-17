<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterSkillProficiency;
use App\Models\ruleset\Skill;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterSkillProficiencyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $skill = Skill::factory()->create();

        $proficiency = CharacterSkillProficiency::factory()->for($character)->create([
            'skill_id' => $skill->id,
            'proficiency_level' => 'expertise',
        ]);

        $this->assertDatabaseHas('character_skill_proficiencies', [
            'id' => $proficiency->id,
            'character_id' => $character->id,
            'skill_id' => $skill->id,
            'proficiency_level' => 'expertise',
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $proficiency = CharacterSkillProficiency::factory()->for($character)->create();

        $this->assertTrue($proficiency->character->is($character));
    }

    public function test_appartient_a_une_competence(): void
    {
        $skill = Skill::factory()->create();
        $proficiency = CharacterSkillProficiency::factory()->create(['skill_id' => $skill->id]);

        $this->assertTrue($proficiency->skill->is($skill));
    }
}
