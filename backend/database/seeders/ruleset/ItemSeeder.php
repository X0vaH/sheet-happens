<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Armor;
use App\Models\ruleset\Item;
use App\Models\ruleset\Tool;
use App\Models\ruleset\Weapon;
use Database\Factories\ruleset\ArmorFactory;
use Database\Factories\ruleset\WeaponFactory;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        $items = [
            'Sac à dos' => ['gear', 2.3, 2],
            'Corde en chanvre (15 m)' => ['gear', 4.5, 1],
            'Torche' => ['gear', 0.45, 0.01],
            'Rations de voyage (1 jour)' => ['consumable', 0.9, 0.5],
            'Potion de soins' => ['consumable', 0.25, 50],
            'Gemme (rubis)' => ['treasure', 0, 100],
            'Sacoche à composantes' => ['gear', 1.4, 25],
            'Outils de voleur' => ['tool', 0.5, 25],
            // Équipement de départ (voir ClassSeeder::seedStartingEquipment).
            'Flèches (20)' => ['gear', 0.5, 1],
            'Carreaux d\'arbalète (20)' => ['gear', 0.75, 1],
            'Paquetage d\'explorateur' => ['gear', 27, 10],
            'Paquetage d\'exploration souterraine' => ['gear', 27, 12],
            'Symbole sacré' => ['gear', 0.5, 5],
            'Grimoire' => ['gear', 1.5, 50],
        ];

        $thievesTools = Tool::query()->where('ruleset_id', $ruleset->id)->where('name', 'Outils de voleur')->firstOrFail();

        foreach ($items as $name => [$type, $weight, $value]) {
            $this->seedOnce(Item::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'item_type' => $type,
                'tool_id' => $type === 'tool' ? $thievesTools->id : null,
                'weight' => $weight,
                'value' => $value,
            ]);
        }

        foreach (array_keys(WeaponFactory::catalog()) as $name) {
            $this->seedOnce(Weapon::class, ['item_id'], WeaponFactory::attributesForWeapon($name, $ruleset));
        }

        foreach (array_keys(ArmorFactory::catalog()) as $name) {
            $this->seedOnce(Armor::class, ['item_id'], ArmorFactory::attributesForArmor($name, $ruleset));
        }
    }
}
