<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\CharacterFeatureChoice;
use App\Models\FeatureOption;
use App\Models\ruleset\Feature;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FeatureOptionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $feature = Feature::factory()->create();

        $option = FeatureOption::factory()->for($feature)->create([
            'name' => 'Domaine de la Vie',
            'description' => 'Choix de domaine divin.',
        ]);

        $this->assertDatabaseHas('feature_options', [
            'id' => $option->id,
            'feature_id' => $feature->id,
            'name' => 'Domaine de la Vie',
            'description' => 'Choix de domaine divin.',
        ]);
    }

    public function test_appartient_a_une_aptitude(): void
    {
        $feature = Feature::factory()->create();
        $option = FeatureOption::factory()->for($feature)->create();

        $this->assertTrue($option->feature->is($feature));
    }

    public function test_a_plusieurs_choix_de_personnage(): void
    {
        $option = FeatureOption::factory()->create();
        CharacterFeatureChoice::factory()->for($option, 'featureOption')->count(2)->create();

        $this->assertCount(2, $option->featureChoices);
        $this->assertContainsOnlyInstancesOf(CharacterFeatureChoice::class, $option->featureChoices);
    }
}
