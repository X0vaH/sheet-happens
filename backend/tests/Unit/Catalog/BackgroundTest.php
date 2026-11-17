<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Tool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BackgroundTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $background = Background::factory()->for($ruleset)->create([
            'name' => 'Acolyte',
            'description' => "Vous avez passé votre jeunesse au service d'un temple.",
        ]);

        $this->assertDatabaseHas('backgrounds', [
            'id' => $background->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Acolyte',
            'description' => "Vous avez passé votre jeunesse au service d'un temple.",
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $background = Background::factory()->for($ruleset)->create();

        $this->assertTrue($background->ruleset->is($ruleset));
    }

    public function test_est_associe_a_plusieurs_caracteristiques(): void
    {
        $background = Background::factory()->create();
        $ability = Ability::factory()->create();
        $background->abilities()->attach($ability);

        $this->assertCount(1, $background->abilities);
        $this->assertTrue($background->abilities->first()->is($ability));
    }

    public function test_est_associe_a_plusieurs_competences(): void
    {
        $background = Background::factory()->create();
        $skill = Skill::factory()->create();
        $background->skills()->attach($skill);

        $this->assertCount(1, $background->skills);
        $this->assertTrue($background->skills->first()->is($skill));
    }

    public function test_est_associe_a_plusieurs_outils(): void
    {
        $background = Background::factory()->create();
        $tool = Tool::factory()->create();
        $background->tools()->attach($tool);

        $this->assertCount(1, $background->tools);
        $this->assertTrue($background->tools->first()->is($tool));
    }

    public function test_est_associe_a_plusieurs_dons(): void
    {
        $background = Background::factory()->create();
        $feat = Feat::factory()->create();
        $background->feats()->attach($feat);

        $this->assertCount(1, $background->feats);
        $this->assertTrue($background->feats->first()->is($feat));
    }
}
