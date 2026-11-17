<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterLevel;
use App\Models\CharacterLevelClass;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterLevelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();

        $level = CharacterLevel::factory()->for($character)->create([
            'character_level' => 5,
            'experience_points' => 6500,
        ]);

        $this->assertDatabaseHas('character_levels', [
            'id' => $level->id,
            'character_id' => $character->id,
            'character_level' => 5,
            'experience_points' => 6500,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $level = CharacterLevel::factory()->for($character)->create();

        $this->assertTrue($level->character->is($character));
    }

    public function test_a_plusieurs_niveaux_de_classe(): void
    {
        $level = CharacterLevel::factory()->create();
        CharacterLevelClass::factory()->for($level, 'characterLevel')->count(2)->create();

        $this->assertCount(2, $level->levelClasses);
        $this->assertContainsOnlyInstancesOf(CharacterLevelClass::class, $level->levelClasses);
    }
}
