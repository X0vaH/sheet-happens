<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterTool;
use App\Models\ruleset\Tool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterToolTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $tool = Tool::factory()->create();

        $characterTool = CharacterTool::factory()->for($character)->create([
            'tool_id' => $tool->id,
            'proficiency_level' => 'proficient',
        ]);

        $this->assertDatabaseHas('character_tools', [
            'id' => $characterTool->id,
            'character_id' => $character->id,
            'tool_id' => $tool->id,
            'proficiency_level' => 'proficient',
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterTool = CharacterTool::factory()->for($character)->create();

        $this->assertTrue($characterTool->character->is($character));
    }

    public function test_appartient_a_un_outil(): void
    {
        $tool = Tool::factory()->create();
        $characterTool = CharacterTool::factory()->create(['tool_id' => $tool->id]);

        $this->assertTrue($characterTool->tool->is($tool));
    }
}
