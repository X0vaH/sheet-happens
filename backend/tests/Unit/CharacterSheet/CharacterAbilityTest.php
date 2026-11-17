<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterAbility;
use App\Models\ruleset\Ability;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterAbilityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $ability = Ability::factory()->create();

        $characterAbility = CharacterAbility::factory()->for($character)->create([
            'ability_id' => $ability->id,
            'score' => 16,
        ]);

        $this->assertDatabaseHas('character_abilities', [
            'id' => $characterAbility->id,
            'character_id' => $character->id,
            'ability_id' => $ability->id,
            'score' => 16,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterAbility = CharacterAbility::factory()->for($character)->create();

        $this->assertTrue($characterAbility->character->is($character));
    }

    public function test_appartient_a_une_caracteristique(): void
    {
        $ability = Ability::factory()->create();
        $characterAbility = CharacterAbility::factory()->create(['ability_id' => $ability->id]);

        $this->assertTrue($characterAbility->ability->is($ability));
    }
}
