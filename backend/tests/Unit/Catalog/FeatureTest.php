<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Species;
use App\Models\ruleset\SubclassLevel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $feature = Feature::factory()->for($ruleset)->create([
            'name' => 'Vision dans le noir',
            'feature_type' => 'species',
            'description' => 'Vous voyez dans le noir.',
        ]);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Vision dans le noir',
            'feature_type' => 'species',
            'description' => 'Vous voyez dans le noir.',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $feature = Feature::factory()->for($ruleset)->create();

        $this->assertTrue($feature->ruleset->is($ruleset));
    }

    public function test_est_associee_a_plusieurs_especes(): void
    {
        $feature = Feature::factory()->create(['feature_type' => 'species']);
        $species = Species::factory()->create();
        $species->features()->attach($feature);

        $this->assertCount(1, $feature->species);
        $this->assertTrue($feature->species->first()->is($species));
    }

    public function test_est_associee_a_plusieurs_niveaux_de_classe(): void
    {
        $feature = Feature::factory()->create(['feature_type' => 'class']);
        $classLevel = ClassLevel::factory()->create();
        $classLevel->features()->attach($feature);

        $this->assertCount(1, $feature->classLevels);
        $this->assertTrue($feature->classLevels->first()->is($classLevel));
    }

    public function test_est_associee_a_plusieurs_niveaux_de_sous_classe(): void
    {
        $feature = Feature::factory()->create(['feature_type' => 'subclass']);
        $subclassLevel = SubclassLevel::factory()->create();
        $subclassLevel->features()->attach($feature);

        $this->assertCount(1, $feature->subclassLevels);
        $this->assertTrue($feature->subclassLevels->first()->is($subclassLevel));
    }
}
