<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ruleset;
use App\Models\ruleset\RuleSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RuleSource>
 */
class RuleSourceFactory extends Factory
{
    protected $model = RuleSource::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = [
            'Manuel des Joueurs (2024)' => '2024-09-17',
            'Guide du Maître (2024)' => '2024-11-12',
            'Manuel des Monstres (2024)' => '2025-02-18',
        ];

        $name = fake()->randomElement(array_keys($sources));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'source_type' => 'book',
            'publication_date' => $sources[$name],
            'description' => "Ouvrage de règles officiel {$name}.",
        ];
    }
}
