<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AbilityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $ability = Ability::factory()->for($ruleset)->create([
            'name' => 'Force',
            'abbreviation' => 'FOR',
        ]);

        $this->assertDatabaseHas('abilities', [
            'id' => $ability->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Force',
            'abbreviation' => 'FOR',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $ability = Ability::factory()->for($ruleset)->create();

        $this->assertTrue($ability->ruleset->is($ruleset));
    }

    public function test_a_plusieurs_competences(): void
    {
        $ability = Ability::factory()->create();
        Skill::factory()->create(['ruleset_id' => $ability->ruleset_id, 'ability_id' => $ability->id]);
        Skill::factory()->create(['ruleset_id' => $ability->ruleset_id, 'ability_id' => $ability->id]);

        $this->assertCount(2, $ability->skills);
        $this->assertContainsOnlyInstancesOf(Skill::class, $ability->skills);
    }

    public function test_est_associee_a_plusieurs_historiques(): void
    {
        $ability = Ability::factory()->create();
        $background = Background::factory()->create();
        $background->abilities()->attach($ability);

        $this->assertCount(1, $ability->backgrounds);
        $this->assertTrue($ability->backgrounds->first()->is($background));
    }

    public function test_est_associee_a_plusieurs_classes_via_les_jets_de_sauvegarde(): void
    {
        $ability = Ability::factory()->create();
        $class = CharacterClass::factory()->create();
        $class->savingThrows()->attach($ability);

        $this->assertCount(1, $ability->classes);
        $this->assertTrue($ability->classes->first()->is($class));
    }
}
