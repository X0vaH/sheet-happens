<?php

namespace Database\Seeders;

use Database\Seeders\ruleset\RulesetDatabaseSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RulesetDatabaseSeeder::class);
    }
}
