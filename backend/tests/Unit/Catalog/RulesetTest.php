<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Item;
use App\Models\ruleset\Language;
use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\RuleSource;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Species;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Tool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RulesetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $ruleset = Ruleset::factory()->create([
            'name' => 'Donjons & Dragons',
            'version' => '2024',
            'edition' => '5e',
            'description' => 'Règles de base.',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('rulesets', [
            'id' => $ruleset->id,
            'name' => 'Donjons & Dragons',
            'version' => '2024',
            'edition' => '5e',
            'description' => 'Règles de base.',
            'status' => 'published',
        ]);
    }

    public function test_caste_les_dates_de_publication_et_de_retrait_en_datetime(): void
    {
        $ruleset = Ruleset::factory()->create([
            'published_at' => '2024-09-17',
            'retired_at' => '2025-01-01',
        ]);

        $this->assertInstanceOf(Carbon::class, $ruleset->published_at);
        $this->assertInstanceOf(Carbon::class, $ruleset->retired_at);
    }

    public function test_a_plusieurs_sources_de_regles(): void
    {
        $ruleset = Ruleset::factory()->create();
        RuleSource::factory()->for($ruleset)->create();
        RuleSource::factory()->for($ruleset)->create();

        $this->assertCount(2, $ruleset->ruleSources);
        $this->assertContainsOnlyInstancesOf(RuleSource::class, $ruleset->ruleSources);
    }

    public function test_a_plusieurs_caracteristiques(): void
    {
        $ruleset = Ruleset::factory()->create();
        Ability::factory()->for($ruleset)->create(['name' => 'Force', 'abbreviation' => 'FOR']);
        Ability::factory()->for($ruleset)->create(['name' => 'Dextérité', 'abbreviation' => 'DEX']);

        $this->assertCount(2, $ruleset->abilities);
        $this->assertContainsOnlyInstancesOf(Ability::class, $ruleset->abilities);
    }

    public function test_a_plusieurs_competences(): void
    {
        $ruleset = Ruleset::factory()->create();
        $ability = Ability::factory()->for($ruleset)->create();
        Skill::factory()->for($ruleset)->create(['ability_id' => $ability->id]);

        $this->assertCount(1, $ruleset->skills);
        $this->assertInstanceOf(Skill::class, $ruleset->skills->first());
    }

    public function test_a_plusieurs_langues(): void
    {
        $ruleset = Ruleset::factory()->create();
        Language::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->languages);
        $this->assertInstanceOf(Language::class, $ruleset->languages->first());
    }

    public function test_a_plusieurs_outils(): void
    {
        $ruleset = Ruleset::factory()->create();
        Tool::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->tools);
        $this->assertInstanceOf(Tool::class, $ruleset->tools->first());
    }

    public function test_a_plusieurs_especes(): void
    {
        $ruleset = Ruleset::factory()->create();
        Species::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->species);
        $this->assertInstanceOf(Species::class, $ruleset->species->first());
    }

    public function test_a_plusieurs_historiques(): void
    {
        $ruleset = Ruleset::factory()->create();
        Background::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->backgrounds);
        $this->assertInstanceOf(Background::class, $ruleset->backgrounds->first());
    }

    public function test_a_plusieurs_classes(): void
    {
        $ruleset = Ruleset::factory()->create();
        CharacterClass::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->classes);
        $this->assertInstanceOf(CharacterClass::class, $ruleset->classes->first());
    }

    public function test_a_plusieurs_aptitudes(): void
    {
        $ruleset = Ruleset::factory()->create();
        Feature::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->features);
        $this->assertInstanceOf(Feature::class, $ruleset->features->first());
    }

    public function test_a_plusieurs_dons(): void
    {
        $ruleset = Ruleset::factory()->create();
        Feat::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->feats);
        $this->assertInstanceOf(Feat::class, $ruleset->feats->first());
    }

    public function test_a_plusieurs_niveaux(): void
    {
        $ruleset = Ruleset::factory()->create();
        Level::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->levels);
        $this->assertInstanceOf(Level::class, $ruleset->levels->first());
    }

    public function test_a_plusieurs_sorts(): void
    {
        $ruleset = Ruleset::factory()->create();
        Spell::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->spells);
        $this->assertInstanceOf(Spell::class, $ruleset->spells->first());
    }

    public function test_a_plusieurs_objets(): void
    {
        $ruleset = Ruleset::factory()->create();
        Item::factory()->for($ruleset)->create();

        $this->assertCount(1, $ruleset->items);
        $this->assertInstanceOf(Item::class, $ruleset->items->first());
    }

    public function test_supprimer_un_ruleset_supprime_ses_aptitudes_en_cascade(): void
    {
        $ruleset = Ruleset::factory()->create();
        $ability = Ability::factory()->for($ruleset)->create();

        $ruleset->delete();

        $this->assertModelMissing($ability);
    }
}
