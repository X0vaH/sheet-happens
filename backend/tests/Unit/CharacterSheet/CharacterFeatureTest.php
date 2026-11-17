<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterFeature;
use App\Models\CharacterFeatureChoice;
use App\Models\ruleset\Feature;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $feature = Feature::factory()->create();

        $characterFeature = CharacterFeature::factory()->for($character)->create([
            'feature_id' => $feature->id,
            'acquired_at_character_level' => 1,
        ]);

        $this->assertDatabaseHas('character_features', [
            'id' => $characterFeature->id,
            'character_id' => $character->id,
            'feature_id' => $feature->id,
            'acquired_at_character_level' => 1,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterFeature = CharacterFeature::factory()->for($character)->create();

        $this->assertTrue($characterFeature->character->is($character));
    }

    public function test_appartient_a_une_aptitude(): void
    {
        $feature = Feature::factory()->create();
        $characterFeature = CharacterFeature::factory()->create(['feature_id' => $feature->id]);

        $this->assertTrue($characterFeature->feature->is($feature));
    }

    public function test_a_plusieurs_choix(): void
    {
        $characterFeature = CharacterFeature::factory()->create();
        CharacterFeatureChoice::factory()->for($characterFeature, 'characterFeature')->count(2)->create();

        $this->assertCount(2, $characterFeature->choices);
        $this->assertContainsOnlyInstancesOf(CharacterFeatureChoice::class, $characterFeature->choices);
    }
}
