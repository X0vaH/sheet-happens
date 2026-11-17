<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterDetail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterDetailTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();

        $detail = CharacterDetail::factory()->for($character)->create([
            'ideals' => 'La justice avant tout.',
            'bonds' => 'Protéger son village natal.',
            'flaws' => 'Trop impulsif.',
        ]);

        $this->assertDatabaseHas('character_details', [
            'id' => $detail->id,
            'character_id' => $character->id,
            'ideals' => 'La justice avant tout.',
            'bonds' => 'Protéger son village natal.',
            'flaws' => 'Trop impulsif.',
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $detail = CharacterDetail::factory()->for($character)->create();

        $this->assertTrue($detail->character->is($character));
    }
}
