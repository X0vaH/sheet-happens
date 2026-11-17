<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubclassTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $classLevel = ClassLevel::factory()->create();

        $subclass = Subclass::factory()->create([
            'class_level_id' => $classLevel->id,
            'name' => 'Voie du Berserker',
            'description' => 'Une tradition de barbares.',
        ]);

        $this->assertDatabaseHas('subclasses', [
            'id' => $subclass->id,
            'class_level_id' => $classLevel->id,
            'name' => 'Voie du Berserker',
            'description' => 'Une tradition de barbares.',
        ]);
    }

    public function test_appartient_a_un_niveau_de_classe(): void
    {
        $classLevel = ClassLevel::factory()->create();
        $subclass = Subclass::factory()->create(['class_level_id' => $classLevel->id]);

        $this->assertTrue($subclass->classLevel->is($classLevel));
    }

    public function test_a_plusieurs_niveaux_de_sous_classe(): void
    {
        $subclass = Subclass::factory()->create();
        SubclassLevel::factory()->create(['subclass_id' => $subclass->id]);

        $this->assertCount(1, $subclass->subclassLevels);
        $this->assertInstanceOf(SubclassLevel::class, $subclass->subclassLevels->first());
    }

    public function test_est_associee_a_plusieurs_sorts(): void
    {
        $subclass = Subclass::factory()->create();
        $spell = Spell::factory()->create();
        $subclass->spells()->attach($spell);

        $this->assertCount(1, $subclass->spells);
        $this->assertTrue($subclass->spells->first()->is($spell));
    }
}
