<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Background;
use App\Models\ruleset\Item;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Tool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ToolTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $tool = Tool::factory()->for($ruleset)->create([
            'name' => 'Outils de voleur',
            'tool_category' => 'kit',
        ]);

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Outils de voleur',
            'tool_category' => 'kit',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $tool = Tool::factory()->for($ruleset)->create();

        $this->assertTrue($tool->ruleset->is($ruleset));
    }

    public function test_est_associe_a_plusieurs_historiques(): void
    {
        $tool = Tool::factory()->create();
        $background = Background::factory()->create();
        $background->tools()->attach($tool);

        $this->assertCount(1, $tool->backgrounds);
        $this->assertTrue($tool->backgrounds->first()->is($background));
    }

    public function test_a_plusieurs_objets(): void
    {
        $tool = Tool::factory()->create();
        Item::factory()->create(['ruleset_id' => $tool->ruleset_id, 'item_type' => 'tool', 'tool_id' => $tool->id]);

        $this->assertCount(1, $tool->items);
        $this->assertInstanceOf(Item::class, $tool->items->first());
    }

    public function test_supprimer_le_ruleset_supprime_l_outil_en_cascade(): void
    {
        $ruleset = Ruleset::factory()->create();
        $tool = Tool::factory()->for($ruleset)->create();

        $ruleset->delete();

        $this->assertModelMissing($tool);
    }
}
