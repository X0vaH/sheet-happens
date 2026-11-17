<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterInventory;
use App\Models\ruleset\Item;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterInventoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $character = Character::factory()->create();
        $item = Item::factory()->create();

        $inventory = CharacterInventory::factory()->for($character)->create([
            'item_id' => $item->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('character_inventory', [
            'id' => $inventory->id,
            'character_id' => $character->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_appartient_a_un_personnage(): void
    {
        $character = Character::factory()->create();
        $inventory = CharacterInventory::factory()->for($character)->create();

        $this->assertTrue($inventory->character->is($character));
    }

    public function test_appartient_a_un_objet(): void
    {
        $item = Item::factory()->create();
        $inventory = CharacterInventory::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($inventory->item->is($item));
    }
}
