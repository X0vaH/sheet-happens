<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Skill;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

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

        foreach ($skills as $name => $abilityName) {
            $ability = Ability::query()->where('ruleset_id', $ruleset->id)->where('name', $abilityName)->firstOrFail();

            $this->seedOnce(Skill::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'ability_id' => $ability->id,
            ]);
        }
    }
}
