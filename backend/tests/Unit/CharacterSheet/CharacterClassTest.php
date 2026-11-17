<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterClass;
use App\Models\CharacterLevelClass;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\Subclass;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterClassTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $classDefinition = ClassDefinition::factory()->create();

        $characterClass = CharacterClass::factory()->for($character)->create([
            'class_id' => $classDefinition->id,
            'subclass_id' => null,
            'first_taken_at_character_level' => 1,
        ]);

        $this->assertDatabaseHas('character_classes', [
            'id' => $characterClass->id,
            'character_id' => $character->id,
            'class_id' => $classDefinition->id,
            'subclass_id' => null,
            'first_taken_at_character_level' => 1,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $characterClass = CharacterClass::factory()->for($character)->create();

        $this->assertTrue($characterClass->character->is($character));
    }

    public function test_appartient_a_une_definition_de_classe_du_catalogue(): void
    {
        $classDefinition = ClassDefinition::factory()->create();
        $characterClass = CharacterClass::factory()->create(['class_id' => $classDefinition->id]);

        $this->assertTrue($characterClass->classDefinition->is($classDefinition));
    }

    public function test_appartient_a_une_sous_classe(): void
    {
        $subclass = Subclass::factory()->create();
        $characterClass = CharacterClass::factory()->create(['subclass_id' => $subclass->id]);

        $this->assertTrue($characterClass->subclass->is($subclass));
    }

    public function test_a_plusieurs_niveaux_de_classe(): void
    {
        $characterClass = CharacterClass::factory()->create();
        CharacterLevelClass::factory()->for($characterClass, 'characterClass')->count(2)->create();

        $this->assertCount(2, $characterClass->levelClasses);
        $this->assertContainsOnlyInstancesOf(CharacterLevelClass::class, $characterClass->levelClasses);
    }
}
