<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Background;
use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Background>
 */
class BackgroundFactory extends Factory
{
    protected $model = Background::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $backgrounds = [
            'Acolyte' => "Vous avez passé votre jeunesse au service d'un temple, assistant les prêtres dans les rites sacrés.",
            'Soldat' => 'Vous avez servi dans une armée, appris la discipline militaire et le maniement des armes.',
            'Sage' => 'Vous avez passé des années à étudier les arcanes du savoir dans une bibliothèque ou une université.',
            'Criminel' => 'Vous avez vécu en marge de la loi, maîtrisant la discrétion et les activités illicites.',
            'Guide' => 'Vous avez grandi loin de la civilisation, familier des terres sauvages et de la survie.',
            'Artisan' => "Vous avez appris un métier manuel auprès d'un maître artisan au sein d'une guilde.",
            'Marin' => 'Vous avez navigué sur les mers, affrontant tempêtes et créatures des profondeurs.',
            'Noble' => "Vous êtes né dans une famille de rang, habitué au pouvoir et à l'étiquette de cour.",
        ];

        $name = fake()->randomElement(array_keys($backgrounds));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'description' => $backgrounds[$name],
        ];
    }
}
