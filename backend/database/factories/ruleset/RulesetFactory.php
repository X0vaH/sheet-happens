<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ruleset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ruleset>
 */
class RulesetFactory extends Factory
{
    protected $model = Ruleset::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Donjons & Dragons - Cinquième édition',
            'version' => '2024',
            'edition' => '5e',
            'description' => 'Règles de base de Donjons & Dragons, cinquième édition, révision 2024 (Manuel des Joueurs).',
            'status' => 'published',
            'published_at' => '2024-09-17',
            'retired_at' => null,
        ];
    }
}
