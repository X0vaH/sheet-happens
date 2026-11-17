<?php

namespace Database\Seeders\ruleset;

use App\Models\ruleset\Alignment;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class AlignmentSeeder extends Seeder
{
    use SeedsOnce;

    /**
     * @var array<string, string> nom => description
     */
    public const DEFINITIONS = [
        'Loyal bon' => 'Respecte les règles et cherche à faire le bien et à protéger les autres.',
        'Neutre bon' => 'Cherche avant tout à faire le bien, sans être strictement attaché aux règles.',
        'Chaotique bon' => 'Fait ce qu’il croit juste, même s’il doit désobéir aux règles ou aux autorités.',
        'Loyal neutre' => 'Suit un code, des lois ou des traditions, peu importe qu’ils soient bons ou mauvais.',
        'Neutre' => 'Agit selon les circonstances sans favoriser particulièrement l’ordre, le chaos, le bien ou le mal.',
        'Chaotique neutre' => 'Privilégie sa liberté personnelle et agit selon ses propres envies plutôt que selon les règles.',
        'Loyal mauvais' => 'Utilise les règles, la hiérarchie ou un code pour servir ses propres intérêts au détriment des autres.',
        'Neutre mauvais' => 'Cherche principalement son propre intérêt et n’hésite pas à nuire aux autres pour l’obtenir.',
        'Chaotique mauvais' => 'Agit égoïstement et violemment sans se soucier des règles ni du bien-être des autres.',
    ];

    public function run(): void
    {
        $ruleset = $this->ruleset();

        foreach (self::DEFINITIONS as $name => $description) {
            $this->seedOnce(Alignment::class, ['ruleset_id', 'name'], [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'description' => $description,
            ]);
        }
    }
}
