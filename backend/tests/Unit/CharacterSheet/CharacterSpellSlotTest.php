<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterSpellSlot;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterSpellSlotTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();

        $slot = CharacterSpellSlot::factory()->for($character)->create([
            'spell_level' => 1,
            'max_slots' => 4,
            'expended_slots' => 1,
        ]);

        $this->assertDatabaseHas('character_spell_slots', [
            'id' => $slot->id,
            'character_id' => $character->id,
            'spell_level' => 1,
            'max_slots' => 4,
            'expended_slots' => 1,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $slot = CharacterSpellSlot::factory()->for($character)->create();

        $this->assertTrue($slot->character->is($character));
    }
}
