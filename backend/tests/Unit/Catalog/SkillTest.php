<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();
        $ability = Ability::factory()->for($ruleset)->create();

        $skill = Skill::factory()->create([
            'ruleset_id' => $ruleset->id,
            'name' => 'Perception',
            'ability_id' => $ability->id,
        ]);

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Perception',
            'ability_id' => $ability->id,
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $ability = Ability::factory()->for($ruleset)->create();
        $skill = Skill::factory()->create(['ruleset_id' => $ruleset->id, 'ability_id' => $ability->id]);

        $this->assertTrue($skill->ruleset->is($ruleset));
    }

    public function test_appartient_a_une_caracteristique(): void
    {
        $ability = Ability::factory()->create();
        $skill = Skill::factory()->create(['ruleset_id' => $ability->ruleset_id, 'ability_id' => $ability->id]);

        $this->assertTrue($skill->ability->is($ability));
    }

    public function test_est_associee_a_plusieurs_historiques(): void
    {
        $skill = Skill::factory()->create();
        $background = Background::factory()->create();
        $background->skills()->attach($skill);

        $this->assertCount(1, $skill->backgrounds);
        $this->assertTrue($skill->backgrounds->first()->is($background));
    }
}
