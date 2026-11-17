<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassProficiency;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class ClassProficiencySeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        // Doit tourner après ClassSeeder. classe => [armor => libellés, weapon => libellés].
        $proficiencies = [
            'Barbare' => [
                'armor' => ['Armures légères', 'Armures intermédiaires', 'Boucliers'],
                'weapon' => ['Armes courantes', 'Armes de guerre'],
            ],
            'Clerc' => [
                'armor' => ['Armures légères', 'Armures intermédiaires', 'Boucliers'],
                'weapon' => ['Armes courantes'],
            ],
            'Guerrier' => [
                'armor' => ['Toutes les armures', 'Boucliers'],
                'weapon' => ['Armes courantes', 'Armes de guerre'],
            ],
            'Magicien' => [
                'armor' => [],
                'weapon' => ['Bâtons', 'Dagues', 'Fléchettes', 'Frondes', 'Arbalètes légères'],
            ],
            'Roublard' => [
                'armor' => ['Armures légères'],
                'weapon' => ['Armes courantes', 'Épées longues', 'Épées courtes', 'Rapières', 'Arbalètes de poing'],
            ],
        ];

        foreach ($proficiencies as $className => $types) {
            $class = CharacterClass::query()->where('ruleset_id', $ruleset->id)->where('name', $className)->firstOrFail();

            $rows = [];

            foreach ($types as $type => $labels) {
                foreach ($labels as $label) {
                    $rows[] = ['class_id' => $class->id, 'type' => $type, 'label' => $label];
                }
            }

            $this->seedManyOnce(ClassProficiency::class, ['class_id', 'type', 'label'], $rows);
        }
    }
}
