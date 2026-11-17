<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterClassTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $class = CharacterClass::factory()->for($ruleset)->create([
            'name' => 'Guerrier',
            'hit_die' => 10,
            'description' => "Maître des armes et de l'armure.",
        ]);

        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Guerrier',
            'hit_die' => 10,
            'description' => "Maître des armes et de l'armure.",
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $class = CharacterClass::factory()->for($ruleset)->create();

        $this->assertTrue($class->ruleset->is($ruleset));
    }

    public function test_est_associee_a_plusieurs_caracteristiques_via_jets_de_sauvegarde(): void
    {
        $class = CharacterClass::factory()->create();
        $ability = Ability::factory()->create();
        $class->savingThrows()->attach($ability);

        $this->assertCount(1, $class->savingThrows);
        $this->assertTrue($class->savingThrows->first()->is($ability));
    }

    public function test_a_plusieurs_niveaux_de_classe(): void
    {
        $class = CharacterClass::factory()->create();
        ClassLevel::factory()->create(['class_id' => $class->id]);

        $this->assertCount(1, $class->classLevels);
        $this->assertInstanceOf(ClassLevel::class, $class->classLevels->first());
    }

    public function test_a_plusieurs_sous_classes_via_les_niveaux_de_classe(): void
    {
        $class = CharacterClass::factory()->create();
        $classLevel = ClassLevel::factory()->create(['class_id' => $class->id]);
        $subclass = Subclass::factory()->create(['class_level_id' => $classLevel->id]);

        $this->assertCount(1, $class->subclasses);
        $this->assertTrue($class->subclasses->first()->is($subclass));
    }

    public function test_est_associee_a_plusieurs_sorts(): void
    {
        $class = CharacterClass::factory()->create();
        $spell = Spell::factory()->create();
        $class->spells()->attach($spell);

        $this->assertCount(1, $class->spells);
        $this->assertTrue($class->spells->first()->is($spell));
    }
}
