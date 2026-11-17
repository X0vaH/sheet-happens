<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Armor;
use App\Models\ruleset\Item;
use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Armor>
 */
class ArmorFactory extends Factory
{
    protected $model = Armor::class;

    /**
     * Catalogue réel d'armures 5e : nom => [catégorie, CA, limite de bonus de Dex, force requise,
     * désavantage discrétion, poids kg, valeur po].
     *
     * @return array<string, array{0: string, 1: int, 2: ?int, 3: ?int, 4: bool, 5: float, 6: float}>
     */
    public static function catalog(): array
    {
        return [
            'Cuir' => ['light', 11, null, null, false, 4.5, 10],
            'Cuir clouté' => ['light', 12, null, null, false, 6, 45],
            'Chemise de mailles' => ['medium', 13, 2, null, false, 9, 50],
            'Cuirasse' => ['medium', 14, 2, null, false, 9, 400],
            'Demi-plate' => ['medium', 15, 2, null, true, 18, 750],
            'Cotte de mailles' => ['heavy', 16, 0, 13, true, 25, 75],
            'Armure à lattes' => ['heavy', 17, 0, 15, true, 30, 200],
            'Harnois' => ['heavy', 18, 0, 15, true, 29.5, 1500],
            'Bouclier' => ['shield', 2, null, null, false, 3, 10],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ruleset = Ruleset::query()->first() ?? Ruleset::factory()->create();
        $name = fake()->randomElement(array_keys(static::catalog()));

        return static::attributesForArmor($name, $ruleset);
    }

    /**
     * Construit les attributs complets d'une armure précise du catalogue, en créant (ou réutilisant)
     * son item associé. Utilisé par les seeders pour générer chaque armure réelle sans doublon.
     *
     * @return array<string, mixed>
     */
    public static function attributesForArmor(string $name, Ruleset $ruleset): array
    {
        [$category, $armorClass, $dexLimit, $strRequirement, $stealthDisadvantage, $weight, $value] = static::catalog()[$name];

        $item = Item::query()->firstOrCreate(
            ['ruleset_id' => $ruleset->id, 'name' => $name],
            [
                'item_type' => 'armor',
                'description' => ItemFactory::descriptionFor($name),
                'weight' => $weight,
                'value' => $value,
            ],
        );

        return [
            'item_id' => $item->id,
            'armor_category' => $category,
            'armor_class' => $armorClass,
            'dexterity_bonus_limit' => $dexLimit,
            'strength_requirement' => $strRequirement,
            'stealth_disadvantage' => $stealthDisadvantage,
        ];
    }
}
