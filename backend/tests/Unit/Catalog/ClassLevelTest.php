<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Level;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClassLevelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_appartient_a_une_classe(): void
    {
        $class = CharacterClass::factory()->create();
        $classLevel = ClassLevel::factory()->create(['class_id' => $class->id]);

        $this->assertTrue($classLevel->characterClass->is($class));
    }

    public function test_appartient_a_un_niveau(): void
    {
        $level = Level::factory()->create();
        $classLevel = ClassLevel::factory()->create(['level_id' => $level->id]);

        $this->assertTrue($classLevel->level->is($level));
    }

    public function test_a_plusieurs_sous_classes(): void
    {
        $classLevel = ClassLevel::factory()->create();
        Subclass::factory()->create(['class_level_id' => $classLevel->id]);

        $this->assertCount(1, $classLevel->subclasses);
        $this->assertInstanceOf(Subclass::class, $classLevel->subclasses->first());
    }

    public function test_est_associe_a_plusieurs_aptitudes(): void
    {
        $classLevel = ClassLevel::factory()->create();
        $feature = Feature::factory()->create(['feature_type' => 'class']);
        $classLevel->features()->attach($feature);

        $this->assertCount(1, $classLevel->features);
        $this->assertTrue($classLevel->features->first()->is($feature));
    }

    public function test_est_associe_a_plusieurs_sorts(): void
    {
        $classLevel = ClassLevel::factory()->create();
        $spell = Spell::factory()->create();
        $classLevel->spells()->attach($spell);

        $this->assertCount(1, $classLevel->spells);
        $this->assertTrue($classLevel->spells->first()->is($spell));
    }
}
