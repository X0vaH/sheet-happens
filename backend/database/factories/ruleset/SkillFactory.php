<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $skills = [
            'Acrobaties' => 'Dextérité',
            'Arcanes' => 'Intelligence',
            'Athlétisme' => 'Force',
            'Discrétion' => 'Dextérité',
            'Dressage' => 'Sagesse',
            'Escamotage' => 'Dextérité',
            'Histoire' => 'Intelligence',
            'Intimidation' => 'Charisme',
            'Investigation' => 'Intelligence',
            'Médecine' => 'Sagesse',
            'Nature' => 'Intelligence',
            'Perception' => 'Sagesse',
            'Perspicacité' => 'Sagesse',
            'Persuasion' => 'Charisme',
            'Religion' => 'Intelligence',
            'Représentation' => 'Charisme',
            'Survie' => 'Sagesse',
            'Tromperie' => 'Charisme',
        ];

        $abbreviations = [
            'Force' => 'FOR',
            'Dextérité' => 'DEX',
            'Constitution' => 'CON',
            'Intelligence' => 'INT',
            'Sagesse' => 'SAG',
            'Charisme' => 'CHA',
        ];

        $name = fake()->randomElement(array_keys($skills));
        $abilityName = $skills[$name];

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            // Résolu après coup pour suivre un `ruleset_id` fourni à la création.
            'ability_id' => fn (array $attributes) => Ability::query()->firstOrCreate(
                ['ruleset_id' => $attributes['ruleset_id'], 'name' => $abilityName],
                ['abbreviation' => $abbreviations[$abilityName]],
            )->id,
        ];
    }
}
