<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Armor;
use App\Models\ruleset\Item;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ArmorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $item = Item::factory()->create(['item_type' => 'armor']);

        $armor = Armor::factory()->create([
            'item_id' => $item->id,
            'armor_category' => 'heavy',
            'armor_class' => 16,
            'dexterity_bonus_limit' => 0,
            'strength_requirement' => 13,
            'stealth_disadvantage' => true,
        ]);

        $this->assertDatabaseHas('armor', [
            'id' => $armor->id,
            'item_id' => $item->id,
            'armor_category' => 'heavy',
            'armor_class' => 16,
            'dexterity_bonus_limit' => 0,
            'strength_requirement' => 13,
            'stealth_disadvantage' => true,
        ]);
    }

    public function test_caste_le_desavantage_en_discretion_en_booleen(): void
    {
        $armor = Armor::factory()->create(['stealth_disadvantage' => 1]);

        $this->assertIsBool($armor->stealth_disadvantage);
        $this->assertTrue($armor->stealth_disadvantage);
    }

    public function test_appartient_a_un_objet(): void
    {
        $item = Item::factory()->create(['item_type' => 'armor']);
        $armor = Armor::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($armor->item->is($item));
    }
}
