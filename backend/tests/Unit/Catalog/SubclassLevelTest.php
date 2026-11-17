<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Feature;
use App\Models\ruleset\Level;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubclassLevelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_appartient_a_une_sous_classe(): void
    {
        $subclass = Subclass::factory()->create();
        $subclassLevel = SubclassLevel::factory()->create(['subclass_id' => $subclass->id]);

        $this->assertTrue($subclassLevel->subclass->is($subclass));
    }

    public function test_appartient_a_un_niveau(): void
    {
        $level = Level::factory()->create();
        $subclassLevel = SubclassLevel::factory()->create(['level_id' => $level->id]);

        $this->assertTrue($subclassLevel->level->is($level));
    }

    public function test_est_associe_a_plusieurs_aptitudes(): void
    {
        $subclassLevel = SubclassLevel::factory()->create();
        $feature = Feature::factory()->create(['feature_type' => 'subclass']);
        $subclassLevel->features()->attach($feature);

        $this->assertCount(1, $subclassLevel->features);
        $this->assertTrue($subclassLevel->features->first()->is($feature));
    }

    public function test_est_associe_a_plusieurs_sorts(): void
    {
        $subclassLevel = SubclassLevel::factory()->create();
        $spell = Spell::factory()->create();
        $subclassLevel->spells()->attach($spell);

        $this->assertCount(1, $subclassLevel->spells);
        $this->assertTrue($subclassLevel->spells->first()->is($spell));
    }
}
