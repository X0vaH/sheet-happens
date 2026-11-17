<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterAbilityIncrease;
use App\Models\ruleset\Ability;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterAbilityIncreaseTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $ability = Ability::factory()->create();

        $increase = CharacterAbilityIncrease::factory()->for($character)->create([
            'ability_id' => $ability->id,
            'increase_amount' => 2,
            'acquired_at_character_level' => 4,
        ]);

        $this->assertDatabaseHas('character_ability_increases', [
            'id' => $increase->id,
            'character_id' => $character->id,
            'ability_id' => $ability->id,
            'increase_amount' => 2,
            'acquired_at_character_level' => 4,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $increase = CharacterAbilityIncrease::factory()->for($character)->create();

        $this->assertTrue($increase->character->is($character));
    }

    public function test_appartient_a_une_caracteristique(): void
    {
        $ability = Ability::factory()->create();
        $increase = CharacterAbilityIncrease::factory()->create(['ability_id' => $ability->id]);

        $this->assertTrue($increase->ability->is($ability));
    }
}
