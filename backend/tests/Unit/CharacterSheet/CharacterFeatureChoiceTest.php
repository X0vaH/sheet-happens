<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\CharacterFeature;
use App\Models\CharacterFeatureChoice;
use App\Models\FeatureOption;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterFeatureChoiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $characterFeature = CharacterFeature::factory()->create();
        $option = FeatureOption::factory()->create();

        $choice = CharacterFeatureChoice::factory()->create([
            'character_feature_id' => $characterFeature->id,
            'feature_option_id' => $option->id,
        ]);

        $this->assertDatabaseHas('character_feature_choices', [
            'id' => $choice->id,
            'character_feature_id' => $characterFeature->id,
            'feature_option_id' => $option->id,
        ]);
    }

    public function test_appartient_a_une_aptitude_de_personnage(): void
    {
        $characterFeature = CharacterFeature::factory()->create();
        $choice = CharacterFeatureChoice::factory()->create(['character_feature_id' => $characterFeature->id]);

        $this->assertTrue($choice->characterFeature->is($characterFeature));
    }

    public function test_appartient_a_une_option_d_aptitude(): void
    {
        $option = FeatureOption::factory()->create();
        $choice = CharacterFeatureChoice::factory()->create(['feature_option_id' => $option->id]);

        $this->assertTrue($choice->featureOption->is($option));
    }
}
