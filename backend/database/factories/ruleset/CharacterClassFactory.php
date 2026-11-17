<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterClass>
 */
class CharacterClassFactory extends Factory
{
    protected $model = CharacterClass::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classes = [
            'Barbare' => [12, 'Guerrier primitif qui puise sa force dans une rage incontrôlable.'],
            'Clerc' => [8, 'Champion divin qui manie la magie sacrée au nom de sa divinité.'],
            'Guerrier' => [10, "Maître des armes et de l'armure, expert du combat sous toutes ses formes."],
            'Magicien' => [6, 'Érudit de la magie arcanique, capable de manipuler la trame magique.'],
            'Roublard' => [8, 'Spécialiste de la discrétion, de la ruse et des coups précis.'],
        ];

        $name = fake()->randomElement(array_keys($classes));
        [$hitDie, $description] = $classes[$name];

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'hit_die' => $hitDie,
            'description' => $description,
        ];
    }
}
