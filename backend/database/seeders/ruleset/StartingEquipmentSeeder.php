<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\Item;
use App\Models\ruleset\StartingEquipmentGroup;
use App\Models\ruleset\StartingEquipmentOption;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class StartingEquipmentSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        // Doit tourner après ClassSeeder (classes) et ItemSeeder (objets/armes/armures).
        // classe => liste de groupes ; groupe => [label, liste d'options] ;
        // option => [label, [nom d'objet => quantité]]. Un groupe à une seule option
        // n'est pas un vrai choix : c'est de l'équipement accordé automatiquement.
        $equipment = [
            'Barbare' => [
                ['Arme', [
                    ['Hache d\'armes', ['Hache d\'armes' => 1]],
                    ['Deux hachettes', ['Hachette' => 2]],
                ]],
                ['Équipement d\'aventurier', [
                    ['Paquetage d\'explorateur', ['Paquetage d\'explorateur' => 1]],
                ]],
            ],
            'Clerc' => [
                ['Arme', [
                    ['Masse d\'armes', ['Masse d\'armes' => 1]],
                    ['Épée longue', ['Épée longue' => 1]],
                ]],
                ['Armure', [
                    ['Chemise de mailles et bouclier', ['Chemise de mailles' => 1, 'Bouclier' => 1]],
                    ['Cuir clouté', ['Cuir clouté' => 1]],
                ]],
                ['Équipement d\'aventurier', [
                    ['Paquetage d\'explorateur', ['Paquetage d\'explorateur' => 1]],
                    ['Paquetage d\'exploration souterraine', ['Paquetage d\'exploration souterraine' => 1]],
                ]],
                ['Symbole sacré', [
                    ['Symbole sacré', ['Symbole sacré' => 1]],
                ]],
            ],
            'Guerrier' => [
                ['Armure', [
                    ['Cotte de mailles', ['Cotte de mailles' => 1]],
                    ['Armure de cuir, arc long et flèches', ['Cuir' => 1, 'Arc long' => 1, 'Flèches (20)' => 1]],
                ]],
                ['Arme principale', [
                    ['Épée longue et bouclier', ['Épée longue' => 1, 'Bouclier' => 1]],
                    ['Deux épées longues', ['Épée longue' => 2]],
                ]],
                ['Arme secondaire', [
                    ['Arbalète légère et carreaux', ['Arbalète légère' => 1, 'Carreaux d\'arbalète (20)' => 1]],
                    ['Deux hachettes', ['Hachette' => 2]],
                ]],
                ['Équipement d\'aventurier', [
                    ['Paquetage d\'explorateur', ['Paquetage d\'explorateur' => 1]],
                    ['Paquetage d\'exploration souterraine', ['Paquetage d\'exploration souterraine' => 1]],
                ]],
            ],
            'Magicien' => [
                ['Arme', [
                    ['Dague', ['Dague' => 1]],
                    ['Bâton', ['Bâton' => 1]],
                ]],
                ['Composantes arcaniques', [
                    ['Sacoche à composantes', ['Sacoche à composantes' => 1]],
                ]],
                ['Grimoire', [
                    ['Grimoire', ['Grimoire' => 1]],
                ]],
            ],
            'Roublard' => [
                ['Arme de mêlée', [
                    ['Rapière', ['Rapière' => 1]],
                    ['Épée courte', ['Épée courte' => 1]],
                ]],
                ['Arme à distance', [
                    ['Arc court et flèches', ['Arc court' => 1, 'Flèches (20)' => 1]],
                    ['Deux dagues', ['Dague' => 2]],
                ]],
                ['Équipement d\'aventurier', [
                    ['Paquetage d\'explorateur', ['Paquetage d\'explorateur' => 1]],
                    ['Paquetage d\'exploration souterraine', ['Paquetage d\'exploration souterraine' => 1]],
                ]],
                ['Outils de voleur', [
                    ['Outils de voleur', ['Outils de voleur' => 1]],
                ]],
            ],
        ];

        $items = Item::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name');

        foreach ($equipment as $className => $groups) {
            $class = CharacterClass::query()->where('ruleset_id', $ruleset->id)->where('name', $className)->firstOrFail();

            foreach ($groups as [$groupLabel, $options]) {
                $group = $this->seedOnce(StartingEquipmentGroup::class, ['class_id', 'label'], [
                    'class_id' => $class->id,
                    'label' => $groupLabel,
                ]);

                foreach ($options as [$optionLabel, $itemQuantities]) {
                    $option = $this->seedOnce(StartingEquipmentOption::class, ['starting_equipment_group_id', 'label'], [
                        'starting_equipment_group_id' => $group->id,
                        'label' => $optionLabel,
                    ]);

                    $option->items()->syncWithoutDetaching(
                        collect($itemQuantities)->mapWithKeys(
                            fn (int $quantity, string $itemName) => [$items[$itemName]->id => ['quantity' => $quantity]],
                        ),
                    );
                }
            }
        }
    }
}
