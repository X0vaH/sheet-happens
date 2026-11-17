<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Species>
 */
class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = [
            'Humain' => ['medium', 9, "Adaptables et ambitieux, les humains sont l'espèce la plus répandue de Féerune."],
            'Elfe' => ['medium', 9, "Peuple féerique gracieux, doté d'une longue espérance de vie et d'une affinité avec la magie."],
            'Nain' => ['medium', 7.5, 'Robustes et endurants, les nains vivent dans des cités creusées à même la roche.'],
            'Halfelin' => ['small', 7.5, "Petit peuple discret et chanceux, réputé pour son courage face à l'adversité."],
            'Draconien' => ['medium', 9, "Descendants des dragons, dotés d'une ascendance élémentaire et d'un souffle destructeur."],
            'Gnome' => ['small', 7.5, 'Curieux et inventifs, les gnomes ont une résistance naturelle à la magie.'],
            'Demi-elfe' => ['medium', 9, "Nés de l'union entre humains et elfes, ils allient la polyvalence des deux peuples."],
            'Demi-orc' => ['medium', 9, "Descendants d'orcs et d'humains, réputés pour leur endurance hors du commun."],
            'Tieffelin' => ['medium', 9, 'Marqués par un héritage infernal, ils portent cornes et queue en héritage de leurs ancêtres.'],
        ];

        $name = fake()->randomElement(array_keys($species));
        [$size, $speed, $description] = $species[$name];

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'size_category' => $size,
            'base_speed' => $speed,
            'description' => $description,
        ];
    }
}
