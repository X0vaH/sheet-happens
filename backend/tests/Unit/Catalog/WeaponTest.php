<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Item;
use App\Models\ruleset\Weapon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WeaponTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $item = Item::factory()->create(['item_type' => 'weapon']);

        $weapon = Weapon::factory()->create([
            'item_id' => $item->id,
            'weapon_category' => 'martial',
            'damage_dice' => '1d8',
            'damage_type' => 'slashing',
            'versatile_damage_dice' => '1d10',
            'range_normal' => null,
            'range_long' => null,
            'properties' => ['versatile'],
            'mastery_property' => 'sap',
        ]);

        $this->assertDatabaseHas('weapons', [
            'id' => $weapon->id,
            'item_id' => $item->id,
            'weapon_category' => 'martial',
            'damage_dice' => '1d8',
            'damage_type' => 'slashing',
            'versatile_damage_dice' => '1d10',
            'mastery_property' => 'sap',
        ]);
    }

    public function test_caste_les_proprietes_en_tableau(): void
    {
        $weapon = Weapon::factory()->create(['properties' => ['finesse', 'light', 'thrown']]);

        $this->assertIsArray($weapon->properties);
        $this->assertSame(['finesse', 'light', 'thrown'], $weapon->properties);
    }

    public function test_appartient_a_un_objet(): void
    {
        $item = Item::factory()->create(['item_type' => 'weapon']);
        $weapon = Weapon::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($weapon->item->is($item));
    }
}
