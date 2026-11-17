<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterSpell;
use App\Models\ruleset\Spell;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterSpellTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $spell = Spell::factory()->create();

        $characterSpell = CharacterSpell::factory()->for($character)->create([
            'spell_id' => $spell->id,
            'acquired_at_character_level' => 1,
        ]);

        $this->assertDatabaseHas('character_spells', [
            'id' => $characterSpell->id,
            'character_id' => $character->id,
            'spell_id' => $spell->id,
            'acquired_at_character_level' => 1,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterSpell = CharacterSpell::factory()->for($character)->create();

        $this->assertTrue($characterSpell->character->is($character));
    }

    public function test_appartient_a_un_sort(): void
    {
        $spell = Spell::factory()->create();
        $characterSpell = CharacterSpell::factory()->create(['spell_id' => $spell->id]);

        $this->assertTrue($characterSpell->spell->is($spell));
    }
}
