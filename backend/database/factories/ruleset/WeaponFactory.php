<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Item;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Weapon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Weapon>
 */
class WeaponFactory extends Factory
{
    protected $model = Weapon::class;

    /**
     * Catalogue réel d'armes 5e : nom => [catégorie, dé de dégâts, type de dégâts, dé polyvalent,
     * portée normale, portée longue, propriétés, maîtrise, poids kg, valeur po].
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: ?string, 4: ?int, 5: ?int, 6: array<int, string>, 7: string, 8: float, 9: float}>
     */
    public static function catalog(): array
    {
        return [
            'Dague' => ['simple', '1d4', 'piercing', null, 6, 18, ['finesse', 'light', 'thrown'], 'nick', 0.5, 2],
            'Gourdin' => ['simple', '1d4', 'bludgeoning', null, null, null, ['light'], 'slow', 1, 0.1],
            'Masse d\'armes' => ['simple', '1d6', 'bludgeoning', null, null, null, [], 'sap', 2, 5],
            'Bâton' => ['simple', '1d6', 'bludgeoning', '1d8', null, null, ['versatile'], 'topple', 2, 0.2],
            'Arc court' => ['simple', '1d6', 'piercing', null, 24, 96, ['ammunition', 'two_handed'], 'vex', 1, 25],
            'Épée longue' => ['martial', '1d8', 'slashing', '1d10', null, null, ['versatile'], 'sap', 1.5, 15],
            'Épée courte' => ['martial', '1d6', 'piercing', null, null, null, ['finesse', 'light'], 'vex', 1, 10],
            'Hache d\'armes' => ['martial', '1d8', 'slashing', '1d10', null, null, ['versatile'], 'topple', 2, 10],
            'Rapière' => ['martial', '1d8', 'piercing', null, null, null, ['finesse'], 'vex', 1, 25],
            'Arc long' => ['martial', '1d8', 'piercing', null, 45, 180, ['ammunition', 'heavy', 'two_handed'], 'slow', 1, 50],
            'Hachette' => ['simple', '1d6', 'slashing', null, 6, 18, ['light', 'thrown'], 'vex', 1, 5],
            'Arbalète légère' => ['simple', '1d8', 'piercing', null, 24, 96, ['ammunition', 'loading', 'two_handed'], 'slow', 2.5, 25],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ruleset = Ruleset::query()->first() ?? Ruleset::factory()->create();
        $name = fake()->randomElement(array_keys(static::catalog()));

        return static::attributesForWeapon($name, $ruleset);
    }

    /**
     * Construit les attributs complets d'une arme précise du catalogue, en créant (ou réutilisant)
     * son item associé. Utilisé par les seeders pour générer chaque arme réelle sans doublon.
     *
     * @return array<string, mixed>
     */
    public static function attributesForWeapon(string $name, Ruleset $ruleset): array
    {
        [$category, $damageDice, $damageType, $versatileDice, $rangeNormal, $rangeLong, $properties, $mastery, $weight, $value] = static::catalog()[$name];

        $item = Item::query()->firstOrCreate(
            ['ruleset_id' => $ruleset->id, 'name' => $name],
            [
                'item_type' => 'weapon',
                'description' => ItemFactory::descriptionFor($name),
                'weight' => $weight,
                'value' => $value,
            ],
        );

        return [
            'item_id' => $item->id,
            'weapon_category' => $category,
            'damage_dice' => $damageDice,
            'damage_type' => $damageType,
            'versatile_damage_dice' => $versatileDice,
            'range_normal' => $rangeNormal,
            'range_long' => $rangeLong,
            'properties' => $properties,
            'mastery_property' => $mastery,
        ];
    }
}
