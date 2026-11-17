<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Ability;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class AbilitySeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();

        $abilities = [
            'Force' => 'FOR',
            'Dextérité' => 'DEX',
            'Constitution' => 'CON',
            'Intelligence' => 'INT',
            'Sagesse' => 'SAG',
            'Charisme' => 'CHA',
        ];

        foreach ($abilities as $name => $abbreviation) {
            $this->seedOnce(Ability::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'abbreviation' => $abbreviation,
            ]);
        }
    }
}
