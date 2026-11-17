<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Ruleset;
use App\Models\ruleset\RuleSource;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class RulesetSeeder extends Seeder
{
    use SeedsOnce;

    public const NAME = 'Donjons & Dragons - Cinquième édition';

    public const VERSION = '2024';

    public function run(): void
    {
        $ruleset = $this->seedOnce(Ruleset::class, ['name', 'version'], [
            'name' => self::NAME,
            'version' => self::VERSION,
        ]);

        $this->seedOnce(RuleSource::class, ['ruleset_id', 'name'], [
            'ruleset_id' => $ruleset->id,
            'name' => 'Manuel des Joueurs (2024)',
            'source_type' => 'book',
            'publication_date' => '2024-09-17',
            'description' => 'Ouvrage de règles officiel Manuel des Joueurs (2024).',
        ]);
    }
}
