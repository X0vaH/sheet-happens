<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Feature;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Species;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SpeciesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $species = Species::factory()->for($ruleset)->create([
            'name' => 'Halfelin',
            'size_category' => 'small',
            'base_speed' => 7.5,
            'description' => 'Petit peuple discret.',
        ]);

        $this->assertDatabaseHas('species', [
            'id' => $species->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Halfelin',
            'size_category' => 'small',
            'base_speed' => 7.5,
            'description' => 'Petit peuple discret.',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $species = Species::factory()->for($ruleset)->create();

        $this->assertTrue($species->ruleset->is($ruleset));
    }

    public function test_est_associee_a_plusieurs_aptitudes(): void
    {
        $species = Species::factory()->create();
        $feature = Feature::factory()->create(['feature_type' => 'species']);
        $species->features()->attach($feature);

        $this->assertCount(1, $species->features);
        $this->assertTrue($species->features->first()->is($feature));
    }

    public function test_supprimer_le_ruleset_supprime_l_espece_en_cascade(): void
    {
        $ruleset = Ruleset::factory()->create();
        $species = Species::factory()->for($ruleset)->create();

        $ruleset->delete();

        $this->assertModelMissing($species);
    }
}
