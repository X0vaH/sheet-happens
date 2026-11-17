<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterFeat;
use App\Models\ruleset\Feat;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterFeatTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $feat = Feat::factory()->create();

        $characterFeat = CharacterFeat::factory()->for($character)->create([
            'feat_id' => $feat->id,
            'acquired_at_character_level' => 4,
        ]);

        $this->assertDatabaseHas('character_feats', [
            'id' => $characterFeat->id,
            'character_id' => $character->id,
            'feat_id' => $feat->id,
            'acquired_at_character_level' => 4,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterFeat = CharacterFeat::factory()->for($character)->create();

        $this->assertTrue($characterFeat->character->is($character));
    }

    public function test_appartient_a_un_don(): void
    {
        $feat = Feat::factory()->create();
        $characterFeat = CharacterFeat::factory()->create(['feat_id' => $feat->id]);

        $this->assertTrue($characterFeat->feat->is($feat));
    }
}
