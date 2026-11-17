<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\SubclassLevel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LevelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $level = Level::factory()->for($ruleset)->create([
            'level_number' => 5,
            'proficiency_bonus' => 3,
        ]);

        $this->assertDatabaseHas('levels', [
            'id' => $level->id,
            'ruleset_id' => $ruleset->id,
            'level_number' => 5,
            'proficiency_bonus' => 3,
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $level = Level::factory()->for($ruleset)->create();

        $this->assertTrue($level->ruleset->is($ruleset));
    }

    public function test_a_plusieurs_niveaux_de_classe(): void
    {
        $level = Level::factory()->create();
        ClassLevel::factory()->create(['level_id' => $level->id]);

        $this->assertCount(1, $level->classLevels);
        $this->assertInstanceOf(ClassLevel::class, $level->classLevels->first());
    }

    public function test_a_plusieurs_niveaux_de_sous_classe(): void
    {
        $level = Level::factory()->create();
        SubclassLevel::factory()->create(['level_id' => $level->id]);

        $this->assertCount(1, $level->subclassLevels);
        $this->assertInstanceOf(SubclassLevel::class, $level->subclassLevels->first());
    }

    public function test_supprimer_le_ruleset_supprime_le_niveau_en_cascade(): void
    {
        $ruleset = Ruleset::factory()->create();
        $level = Level::factory()->for($ruleset)->create();

        $ruleset->delete();

        $this->assertModelMissing($level);
    }
}
