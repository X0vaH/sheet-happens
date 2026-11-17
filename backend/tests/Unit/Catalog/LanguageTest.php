<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Language;
use App\Models\ruleset\Ruleset;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create();

        $language = Language::factory()->for($ruleset)->create(['name' => 'Elfique']);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Elfique',
        ]);
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $language = Language::factory()->for($ruleset)->create();

        $this->assertTrue($language->ruleset->is($ruleset));
    }
}
