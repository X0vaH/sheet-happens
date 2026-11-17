<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SpellTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $spell = Spell::factory()->for($ruleset)->create([
            'name' => 'Bouclier',
            'spell_level' => 1,
            'school' => 'abjuration',
            'casting_time' => '1 réaction',
            'range' => 'Personnelle',
            'duration' => '1 round',
            'concentration' => false,
            'ritual' => false,
            'verbal' => true,
            'somatic' => true,
            'material' => false,
            'material_description' => null,
        ]);

        $this->assertDatabaseHas('spells', [
            'id' => $spell->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Bouclier',
            'spell_level' => 1,
            'school' => 'abjuration',
        ]);
    }

    public function test_caste_les_champs_booleens(): void
    {
        $spell = Spell::factory()->create([
            'concentration' => 1,
            'ritual' => 0,
            'verbal' => 1,
            'somatic' => 1,
            'material' => 0,
        ]);

        $this->assertIsBool($spell->concentration);
        $this->assertTrue($spell->concentration);
        $this->assertIsBool($spell->ritual);
        $this->assertFalse($spell->ritual);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $spell = Spell::factory()->for($ruleset)->create();

        $this->assertTrue($spell->ruleset->is($ruleset));
    }

    public function test_est_associe_a_plusieurs_classes(): void
    {
        $spell = Spell::factory()->create();
        $class = CharacterClass::factory()->create();
        $class->spells()->attach($spell);

        $this->assertCount(1, $spell->classes);
        $this->assertTrue($spell->classes->first()->is($class));
    }

    public function test_est_associe_a_plusieurs_niveaux_de_classe(): void
    {
        $spell = Spell::factory()->create();
        $classLevel = ClassLevel::factory()->create();
        $classLevel->spells()->attach($spell);

        $this->assertCount(1, $spell->classLevels);
        $this->assertTrue($spell->classLevels->first()->is($classLevel));
    }

    public function test_est_associe_a_plusieurs_sous_classes(): void
    {
        $spell = Spell::factory()->create();
        $subclass = Subclass::factory()->create();
        $subclass->spells()->attach($spell);

        $this->assertCount(1, $spell->subclasses);
        $this->assertTrue($spell->subclasses->first()->is($subclass));
    }

    public function test_est_associe_a_plusieurs_niveaux_de_sous_classe(): void
    {
        $spell = Spell::factory()->create();
        $subclassLevel = SubclassLevel::factory()->create();
        $subclassLevel->spells()->attach($spell);

        $this->assertCount(1, $spell->subclassLevels);
        $this->assertTrue($spell->subclassLevels->first()->is($subclassLevel));
    }
}
