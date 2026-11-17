<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ruleset;
use App\Models\ruleset\RuleSource;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RuleSourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $source = RuleSource::factory()->for($ruleset)->create([
            'name' => 'Manuel des Joueurs (2024)',
            'source_type' => 'book',
            'description' => 'Ouvrage de règles officiel.',
        ]);

        $this->assertDatabaseHas('rule_sources', [
            'id' => $source->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Manuel des Joueurs (2024)',
            'source_type' => 'book',
            'description' => 'Ouvrage de règles officiel.',
        ]);
    }

    public function test_caste_la_date_de_publication_en_date(): void
    {
        $source = RuleSource::factory()->create(['publication_date' => '2024-09-17']);

        $this->assertInstanceOf(Carbon::class, $source->publication_date);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $source = RuleSource::factory()->for($ruleset)->create();

        $this->assertTrue($source->ruleset->is($ruleset));
    }
}
