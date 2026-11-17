<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Armor;
use App\Models\ruleset\Item;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Tool;
use App\Models\ruleset\Weapon;
use Database\Factories\ruleset\ArmorFactory;
use Database\Factories\ruleset\WeaponFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Les factories ne doivent pas supposer qu'il n'existe qu'un seul ruleset.
 */
class MultiRulesetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_un_outil_est_cree_par_ruleset_pour_un_item_de_type_outil(): void
    {
        [$a, $b] = Ruleset::factory()->count(2)->create();

        $itemA = Item::factory()->create(['ruleset_id' => $a->id, 'name' => 'Outils de voleur', 'item_type' => 'tool']);
        $itemB = Item::factory()->create(['ruleset_id' => $b->id, 'name' => 'Outils de voleur', 'item_type' => 'tool']);

        $this->assertNotSame($itemA->tool_id, $itemB->tool_id);
        $this->assertSame($a->id, Tool::findOrFail($itemA->tool_id)->ruleset_id);
        $this->assertSame($b->id, Tool::findOrFail($itemB->tool_id)->ruleset_id);
    }

    public function test_armes_et_armures_creent_un_item_par_ruleset(): void
    {
        [$a, $b] = Ruleset::factory()->count(2)->create();

        Weapon::factory()->create(WeaponFactory::attributesForWeapon('Rapière', $a));
        Weapon::factory()->create(WeaponFactory::attributesForWeapon('Rapière', $b));
        Armor::factory()->create(ArmorFactory::attributesForArmor('Bouclier', $a));
        Armor::factory()->create(ArmorFactory::attributesForArmor('Bouclier', $b));

        $this->assertSame(1, Item::where('ruleset_id', $a->id)->where('name', 'Rapière')->count());
        $this->assertSame(1, Item::where('ruleset_id', $b->id)->where('name', 'Rapière')->count());
        $this->assertSame(1, Item::where('ruleset_id', $a->id)->where('name', 'Bouclier')->count());
        $this->assertSame(1, Item::where('ruleset_id', $b->id)->where('name', 'Bouclier')->count());
    }

    public function test_une_competence_utilise_la_caracteristique_de_son_ruleset(): void
    {
        [$a, $b] = Ruleset::factory()->count(2)->create();

        $skillB = Skill::factory()->create(['ruleset_id' => $b->id]);

        $this->assertSame($b->id, Ability::findOrFail($skillB->ability_id)->ruleset_id);
    }
}
