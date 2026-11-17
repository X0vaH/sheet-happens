<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Item;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Descriptions 5e (Manuel des Joueurs 2024) de tout le catalogue d'items, armes et armures comprises,
     * indexées par nom. Source unique partagée avec WeaponFactory et ArmorFactory.
     *
     * @var array<string, string>
     */
    public const DESCRIPTIONS = [
        // Équipement d'aventurier
        'Sac à dos' => "Contient jusqu'à 15 kg d'équipement.",
        'Corde en chanvre (15 m)' => 'Force DD 17.',
        'Torche' => 'Lumière vive sur 6 m, faible sur 6 m de plus.',
        'Rations de voyage (1 jour)' => 'Vivres secs pour nourrir une créature une journée.',
        'Potion de soins' => 'Rend 2d4 + 2 points de vie à celui qui la boit.',
        'Gemme (rubis)' => "Pierre précieuse rouge, échangeable contre sa valeur en pièces d'or.",
        'Sacoche à composantes' => 'Contient les composantes matérielles sans coût de vos sorts.',
        'Outils de voleur' => 'Crocheter une serrure ou désamorcer un piège (Dextérité, DD 15).',
        'Flèches (20)' => "Munitions d'arc. On en récupère la moitié après un combat.",
        "Carreaux d'arbalète (20)" => "Munitions d'arbalète. On en récupère la moitié après un combat.",
        "Paquetage d'explorateur" => 'Sac à dos, sac de couchage, gamelle, allume-feu, 10 torches, 10 jours de rations, outre, corde.',
        "Paquetage d'exploration souterraine" => 'Sac à dos, pied-de-biche, 2 huiles, 10 jours de rations, corde, allume-feu, 10 torches, chausse-trappes.',
        'Symbole sacré' => "Focaliseur d'incantation des clercs et des paladins.",
        'Grimoire' => 'Contient 100 pages où un magicien consigne ses sorts.',

        // Armes simples et martiales
        'Dague' => 'Légère, finesse, lancer (6/18 m). Maîtrise Nick.',
        'Gourdin' => 'Cible ralentie de 3 m.',
        "Masse d'armes" => 'La cible attaque avec désavantage.',
        'Bâton' => 'La cible peut tomber à terre.',
        'Arc court' => 'Munitions, deux mains, portée 24/96 m. Maîtrise Vex.',
        'Épée longue' => 'La cible attaque avec désavantage.',
        'Épée courte' => 'Avantage à la prochaine attaque.',
        "Hache d'armes" => 'La cible peut tomber à terre.',
        'Rapière' => 'Avantage à la prochaine attaque.',
        'Arc long' => 'Munitions, lourde, deux mains, portée 45/180 m. Maîtrise Slow.',
        'Hachette' => 'Légère, lancer (6/18 m). Maîtrise Vex.',
        'Arbalète légère' => 'Munitions, chargement, deux mains, portée 24/96 m. Maîtrise Slow.',

        // Armures et boucliers
        'Cuir' => 'CA 11 + Dex.',
        'Cuir clouté' => 'CA 12 + Dex.',
        'Chemise de mailles' => 'CA 13 + Dex (max 2).',
        'Cuirasse' => 'CA 14 + Dex (max 2).',
        'Demi-plate' => 'CA 15 + Dex (max 2). Désavantage en Discrétion.',
        'Cotte de mailles' => 'CA 16, Force 13. Désavantage en Discrétion.',
        'Armure à lattes' => 'CA 17, Force 15. Désavantage en Discrétion.',
        'Harnois' => 'CA 18, Force 15. Désavantage en Discrétion.',
        'Bouclier' => 'Bonus de +2 à la CA tant qu\'il est brandi.',
    ];

    /**
     * Description 5e d'un item du catalogue ; retombe sur son nom pour un item inconnu (ex. : créé par un test).
     */
    public static function descriptionFor(string $name): string
    {
        return self::DESCRIPTIONS[$name] ?? $name;
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // nom => [type, poids en kg, valeur en po]
        $items = [
            'Sac à dos' => ['gear', 2.3, 2],
            'Corde en chanvre (15 m)' => ['gear', 4.5, 1],
            'Torche' => ['gear', 0.45, 0.01],
            'Rations de voyage (1 jour)' => ['consumable', 0.9, 0.5],
            'Potion de soins' => ['consumable', 0.25, 50],
            'Gemme (rubis)' => ['treasure', 0, 100],
            'Sacoche à composantes' => ['gear', 1.4, 25],
            'Outils de voleur' => ['tool', 0.5, 25],
        ];

        $name = fake()->randomElement(array_keys($items));
        [$type, $weight, $value] = $items[$name];
        $rulesetId = Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id;

        return [
            'ruleset_id' => $rulesetId,
            'name' => $name,
            'item_type' => $type,
            // Résolu après coup pour suivre les `ruleset_id`, `name` et `item_type` fournis à la création.
            'tool_id' => fn (array $attributes) => $attributes['item_type'] === 'tool'
                ? Tool::query()->firstOrCreate(
                    ['ruleset_id' => $attributes['ruleset_id'], 'name' => $attributes['name']],
                    ['tool_category' => 'kit'],
                )->id
                : null,
            'description' => fn (array $attributes) => static::descriptionFor($attributes['name']),
            'weight' => $weight,
            'value' => $value,
        ];
    }
}
