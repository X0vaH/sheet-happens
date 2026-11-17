<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Background;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Ruleset;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FeatTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $feat = Feat::factory()->for($ruleset)->create([
            'name' => 'Chanceux',
            'category' => 'origin',
            'description' => 'Vous disposez de points de chance.',
        ]);

        $this->assertDatabaseHas('feats', [
            'id' => $feat->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Chanceux',
            'category' => 'origin',
            'description' => 'Vous disposez de points de chance.',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $feat = Feat::factory()->for($ruleset)->create();

        $this->assertTrue($feat->ruleset->is($ruleset));
    }

    public function test_est_associe_a_plusieurs_historiques(): void
    {
        $feat = Feat::factory()->create();
        $background = Background::factory()->create();
        $background->feats()->attach($feat);

        $this->assertCount(1, $feat->backgrounds);
        $this->assertTrue($feat->backgrounds->first()->is($background));
    }
}
