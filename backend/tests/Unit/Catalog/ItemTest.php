<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Armor;
use App\Models\ruleset\Item;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Tool;
use App\Models\ruleset\Weapon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $item = Item::factory()->for($ruleset)->create([
            'name' => 'Sac à dos',
            'item_type' => 'gear',
            'tool_id' => null,
            'description' => 'Un sac utilitaire.',
            'weight' => 2.3,
            'value' => 2,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Sac à dos',
            'item_type' => 'gear',
            'description' => 'Un sac utilitaire.',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $item = Item::factory()->for($ruleset)->create();

        $this->assertTrue($item->ruleset->is($ruleset));
    }

    public function test_appartient_a_un_outil_quand_c_est_un_objet_outil(): void
    {
        $tool = Tool::factory()->create();
        $item = Item::factory()->create([
            'ruleset_id' => $tool->ruleset_id,
            'item_type' => 'tool',
            'tool_id' => $tool->id,
        ]);

        $this->assertTrue($item->tool->is($tool));
    }

    public function test_a_une_arme_associee(): void
    {
        $item = Item::factory()->create(['item_type' => 'weapon']);
        $weapon = Weapon::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($item->weapon->is($weapon));
    }

    public function test_a_une_armure_associee(): void
    {
        $item = Item::factory()->create(['item_type' => 'armor']);
        $armor = Armor::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($item->armor->is($armor));
    }
}
