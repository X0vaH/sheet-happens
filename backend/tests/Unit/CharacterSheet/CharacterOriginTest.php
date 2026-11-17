<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterOrigin;
use App\Models\ruleset\Background;
use App\Models\ruleset\Species;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterOriginTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $specie = Species::factory()->create();
        $background = Background::factory()->create();

        $origin = CharacterOrigin::factory()->for($character)->create([
            'specie_id' => $specie->id,
            'background_id' => $background->id,
        ]);

        $this->assertDatabaseHas('character_origins', [
            'id' => $origin->id,
            'character_id' => $character->id,
            'specie_id' => $specie->id,
            'background_id' => $background->id,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $origin = CharacterOrigin::factory()->for($character)->create();

        $this->assertTrue($origin->character->is($character));
    }

    public function test_appartient_a_une_espece(): void
    {
        $specie = Species::factory()->create();
        $origin = CharacterOrigin::factory()->create(['specie_id' => $specie->id]);

        $this->assertTrue($origin->specie->is($specie));
    }

    public function test_appartient_a_un_historique(): void
    {
        $background = Background::factory()->create();
        $origin = CharacterOrigin::factory()->create(['background_id' => $background->id]);

        $this->assertTrue($origin->background->is($background));
    }
}
