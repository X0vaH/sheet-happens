<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\CharacterClass;
use App\Models\CharacterLevel;
use App\Models\CharacterLevelClass;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterLevelClassTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $characterClass = CharacterClass::factory()->create();
        $characterLevel = CharacterLevel::factory()->create();

        $levelClass = CharacterLevelClass::factory()->create([
            'character_class_id' => $characterClass->id,
            'character_level_id' => $characterLevel->id,
            'resulting_class_level' => 3,
        ]);

        $this->assertDatabaseHas('character_level_classes', [
            'id' => $levelClass->id,
            'character_class_id' => $characterClass->id,
            'character_level_id' => $characterLevel->id,
            'resulting_class_level' => 3,
        ]);
    }

    public function test_appartient_a_une_classe_de_personnage(): void
    {
        $characterClass = CharacterClass::factory()->create();
        $levelClass = CharacterLevelClass::factory()->create(['character_class_id' => $characterClass->id]);

        $this->assertTrue($levelClass->characterClass->is($characterClass));
    }

    public function test_appartient_a_un_niveau_de_personnage(): void
    {
        $characterLevel = CharacterLevel::factory()->create();
        $levelClass = CharacterLevelClass::factory()->create(['character_level_id' => $characterLevel->id]);

        $this->assertTrue($levelClass->characterLevel->is($characterLevel));
    }
}
