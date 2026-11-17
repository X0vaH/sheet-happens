<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterLanguage;
use App\Models\ruleset\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterLanguageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $language = Language::factory()->create();

        $characterLanguage = CharacterLanguage::factory()->for($character)->create([
            'language_id' => $language->id,
            'source' => 'species',
        ]);

        $this->assertDatabaseHas('character_languages', [
            'id' => $characterLanguage->id,
            'character_id' => $character->id,
            'language_id' => $language->id,
            'source' => 'species',
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterLanguage = CharacterLanguage::factory()->for($character)->create();

        $this->assertTrue($characterLanguage->character->is($character));
    }

    public function test_appartient_a_une_langue(): void
    {
        $language = Language::factory()->create();
        $characterLanguage = CharacterLanguage::factory()->create(['language_id' => $language->id]);

        $this->assertTrue($characterLanguage->language->is($language));
    }
}
