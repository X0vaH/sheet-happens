<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\FeatureOption;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Spell;
use App\Models\User;
use Database\Seeders\ruleset\RulesetDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LevelUpControllerTest extends TestCase
{
    use RefreshDatabase;

    private function hero(string $name = 'Guerrier'): Character
    {
        $class = CharacterClass::where('name', $name)->firstOrFail();
        $character = Character::factory()->create(['ruleset_id' => $class->ruleset_id, 'max_health_points' => 10, 'current_health_points' => 8]);
        $character->classes()->create(['class_id' => $class->id, 'first_taken_at_character_level' => 1]);
        $character->levels()->create(['character_level' => 1, 'experience_points' => 0]);
        foreach ($class->ruleset->abilities as $ability) {
            $character->abilities()->create(['ability_id' => $ability->id, 'score' => 14]);
        }

        return $character;
    }

    private function choices(array $options): array
    {
        $spells = collect($options['available_options']['spells']);

        return [
            'target_level' => $options['next_level'], 'hit_point_method' => 'fixed',
            'feat_id' => $options['available_options']['feats'][0]['id'] ?? null,
            'feature_choices' => collect($options['available_options']['features'])->filter(fn ($feature) => $feature['choice_count'] > 0)
                ->map(fn ($feature) => ['feature_id' => $feature['id'], 'option_ids' => array_slice(array_column($feature['options'], 'id'), 0, $feature['choice_count'])])->values()->all(),
            'subclass_id' => $options['available_options']['subclasses'][0]['id'] ?? null,
            'ability_increases' => $options['progression']['ability_points'] ? [[
                'ability_id' => collect($options['available_options']['abilities'])->firstWhere('name', 'Constitution')['id'],
                'increase_amount' => 2,
            ]] : [],
            'spell_ids' => $spells->where('spell_level', 0)->take($options['progression']['cantrip_count'])
                ->concat($spells->where('spell_level', '>', 0)->take($options['progression']['spell_count']))->pluck('id')->all(),
        ];
    }

    public function test_all_seeded_classes_can_progress_from_one_to_five_and_reload(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(RulesetDatabaseSeeder::class);
        foreach (CharacterClass::all() as $class) {
            $hero = $this->hero($class->name);
            $this->actingAs($hero->user);
            $url = '/api/characters/'.$hero->id;
            $expectedHp = 10;
            for ($level = 2; $level <= 5; $level++) {
                $options = $this->getJson($url.'/level-up/options')->assertOk()->json();
                $this->assertSame($level, $options['next_level']);
                $payload = $this->choices($options);
                $this->postJson($url.'/level-up', $payload)->assertOk();
                $expectedHp += intdiv($class->hit_die, 2) + 1 + ($level >= 4 ? 3 : 2) + ($level === 4 ? 3 : 0);
                $this->assertSame($expectedHp, $hero->fresh()->max_health_points);
                if ($level === 4) {
                    $this->assertDatabaseHas('character_feats', ['character_id' => $hero->id, 'feat_id' => $payload['feat_id'], 'acquired_at_character_level' => 4]);
                }
                foreach ($payload['feature_choices'] as $choice) {
                    $acquired = $hero->features()->where('feature_id', $choice['feature_id'])->firstOrFail();
                    $this->assertEqualsCanonicalizing($choice['option_ids'], $acquired->choices()->pluck('feature_option_id')->all());
                }
                foreach ($options['progression']['slots'] as $index => $count) {
                    $this->assertDatabaseHas('character_spell_slots', ['character_id' => $hero->id, 'spell_level' => $index + 1, 'max_slots' => $count]);
                }
                foreach (array_merge($payload['spell_ids'], array_column($options['granted_spells'], 'id')) as $spellId) {
                    $this->assertDatabaseHas('character_spells', ['character_id' => $hero->id, 'spell_id' => $spellId]);
                }
                if ($options['available_options']['subclasses']) {
                    $this->assertSame($options['available_options']['subclasses'][0]['id'], $hero->classes()->first()->subclass_id);
                }
                $sheet = $this->getJson($url)->assertOk()->json('character');
                $this->assertSame($level, collect($sheet['levels'])->max('character_level'));
                $constitution = collect($sheet['abilities'])->firstWhere('ability.name', 'Constitution');
                $increase = collect($sheet['ability_increases'])->where('ability_id', $constitution['ability_id'])->sum('increase_amount');
                $this->assertSame($level >= 4 ? 16 : 14, $constitution['score'] + $increase);
                $this->assertSame(2, $hero->fresh()->max_health_points - $hero->fresh()->current_health_points);
                foreach ($options['available_options']['features'] as $feature) {
                    $this->assertDatabaseHas('character_features', ['character_id' => $hero->id, 'feature_id' => $feature['id']]);
                }
                $this->postJson($url.'/level-up', $payload)->assertUnprocessable();
            }
            $this->getJson($url.'/level-up/options')->assertUnprocessable();
            $this->assertSame(5, $hero->levels()->count());
        }
    }

    public function test_invalid_choices_are_rejected_without_changing_character(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $valid = $this->choices($this->getJson($url.'/options')->assertOk()->json());
        foreach ([['target_level' => 4], ['spell_ids' => [99999]], ['subclass_id' => 99999],
            ['hit_point_method' => 'roll', 'hit_point_roll' => 12],
            ['ability_increases' => [['ability_id' => $hero->abilities()->first()->ability_id, 'increase_amount' => 2]]],
        ] as $invalid) {
            $this->postJson($url, array_replace($valid, $invalid))->assertUnprocessable();
        }
        $this->assertSame(10, $hero->fresh()->max_health_points);
        $this->assertSame(1, $hero->levels()->count());
        $this->postJson($url, $valid)->assertOk();
        $this->assertSame(18, $hero->fresh()->max_health_points);
    }

    public function test_characters_are_private(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $url = '/api/characters/'.$hero->id;
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/characters')->assertOk()->assertExactJson(['characters' => []]);
        $this->getJson($url)->assertNotFound();
        $this->getJson($url.'/level-up/options')->assertNotFound();
        $this->postJson($url.'/level-up', [])->assertNotFound();
    }

    public function test_feature_choices_are_required_validated_and_saved_with_automatic_subclass(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $hero->levels()->create(['character_level' => 2, 'experience_points' => 0]);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $options = $this->getJson($url.'/options')->assertOk()->json();
        $valid = $this->choices($options);
        unset($valid['subclass_id']);
        $this->assertCount(2, $valid['feature_choices']);
        $this->postJson($url, [...$valid, 'feature_choices' => []])->assertUnprocessable();
        $invalid = $valid;
        $invalid['feature_choices'][0]['option_ids'][0] = 99999;
        $this->postJson($url, $invalid)->assertUnprocessable();
        $invalid = $valid;
        $invalid['feature_choices'][0]['option_ids'][0] = $valid['feature_choices'][1]['option_ids'][0];
        $this->postJson($url, $invalid)->assertUnprocessable();
        $this->assertSame(10, $hero->fresh()->max_health_points);
        $this->assertSame(0, $hero->features()->count());
        $this->postJson($url, $valid)->assertOk();
        $this->assertNotNull($hero->classes()->first()->subclass_id);
        $this->assertSame(1, $hero->tools()->count());
    }

    public function test_level_four_requires_a_general_feat_and_scores_cannot_exceed_twenty(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $hero->levels()->create(['character_level' => 3, 'experience_points' => 0]);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $valid = $this->choices($this->getJson($url.'/options')->assertOk()->json());
        $this->postJson($url, [...$valid, 'feat_id' => null])->assertUnprocessable();
        $originFeat = Feat::where('category', 'origin')->firstOrFail();
        $this->postJson($url, [...$valid, 'feat_id' => $originFeat->id])->assertUnprocessable();
        $hero->abilities()->where('ability_id', $valid['ability_increases'][0]['ability_id'])->update(['score' => 19]);
        $this->postJson($url, $valid)->assertUnprocessable();
        $this->assertSame(0, $hero->feats()->count());
        $this->assertSame(10, $hero->fresh()->max_health_points);
    }

    public function test_spells_must_be_new_accessible_and_from_the_class(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero('Magicien');
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $valid = $this->choices($this->getJson($url.'/options')->assertOk()->json());
        $this->postJson($url, [...$valid, 'spell_ids' => []])->assertUnprocessable();
        foreach (['Boule de feu', 'Soins'] as $name) {
            $spell = Spell::where('name', $name)->firstOrFail();
            $this->postJson($url, [...$valid, 'spell_ids' => [$spell->id, $valid['spell_ids'][1]]])->assertUnprocessable();
        }
        $hero->spells()->create(['spell_id' => $valid['spell_ids'][0], 'acquired_at_character_level' => 1]);
        $this->postJson($url, $valid)->assertUnprocessable();
        $this->assertSame(1, $hero->levels()->count());
    }

    public function test_the_sheet_returns_the_updated_stats_after_a_level_up(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $hero->levels()->create(['character_level' => 3, 'experience_points' => 0]);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id;

        $before = $this->getJson($url)->assertOk()->json('character');
        $this->assertSame(4, $before['next_level']);
        $this->assertSame(14, collect($before['abilities'])->firstWhere('ability.name', 'Constitution')['effective_score']);
        $this->assertSame(4, collect($before['saving_throws'])->firstWhere('ability', 'Constitution')['bonus']);

        // Niveau 4 : +2 en Constitution (voir choices()).
        $options = $this->getJson($url.'/level-up/options')->assertOk()->json();
        $this->postJson($url.'/level-up', $this->choices($options))->assertOk();

        $afterFour = $this->getJson($url)->assertOk()->json('character');
        $this->assertSame(5, $afterFour['next_level']);
        $this->assertSame(16, collect($afterFour['abilities'])->firstWhere('ability.name', 'Constitution')['effective_score']);
        $this->assertSame(5, collect($afterFour['saving_throws'])->firstWhere('ability', 'Constitution')['bonus']);
        $this->assertSame(2, $afterFour['proficiency_bonus']);

        // Niveau 5 : le bonus de maîtrise passe à +3 et le niveau maximum est atteint.
        $options = $this->getJson($url.'/level-up/options')->assertOk()->json();
        $this->postJson($url.'/level-up', $this->choices($options))->assertOk();

        $afterFive = $this->getJson($url)->assertOk()->json('character');
        $this->assertNull($afterFive['next_level']);
        $this->assertSame(3, $afterFive['proficiency_bonus']);
        $this->assertSame(6, collect($afterFive['saving_throws'])->firstWhere('ability', 'Constitution')['bonus']);
    }

    public function test_level_four_offers_the_supported_2014_feats_and_a_feat_replaces_ability_increases(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $hero->levels()->create(['character_level' => 3, 'experience_points' => 0]);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $options = $this->getJson($url.'/options')->assertOk()->json();
        $feats = collect($options['available_options']['feats']);
        $this->assertCount(11, $feats);
        $this->assertSame(['general'], $feats->pluck('category')->unique()->values()->all());
        $sentinel = $feats->firstWhere('name', 'Sentinelle');
        $this->assertSame(0, $sentinel['ability_increase_count']);
        $valid = [...$this->choices($options), 'feat_id' => $sentinel['id']];
        $this->postJson($url, $valid)->assertUnprocessable()->assertJsonValidationErrors('ability_increases');
        $this->postJson($url, [...$valid, 'ability_increases' => []])->assertOk();
        $this->assertDatabaseHas('character_feats', ['character_id' => $hero->id, 'feat_id' => $sentinel['id'], 'acquired_at_character_level' => 4]);
        $this->assertSame(0, $hero->abilityIncreases()->count());
        $this->assertSame(18, $hero->fresh()->max_health_points);
        $this->assertSame(16, $hero->fresh()->current_health_points);
    }

    public function test_acquired_and_unsupported_feats_cannot_be_selected(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $hero->levels()->create(['character_level' => 3, 'experience_points' => 0]);
        $owned = Feat::where('name', 'Sentinelle')->firstOrFail();
        $hero->feats()->create(['feat_id' => $owned->id, 'acquired_at_character_level' => 1]);
        $unsupported = Feat::create(['ruleset_id' => $hero->ruleset_id, 'name' => 'Don non pris en charge', 'category' => 'general', 'description' => 'Un don absent du catalogue pris en charge.']);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $options = $this->getJson($url.'/options')->assertOk()->json();
        $ids = array_column($options['available_options']['feats'], 'id');
        foreach ([$owned->id, $unsupported->id] as $id) {
            $this->assertNotContains($id, $ids);
            $this->postJson($url, [...$this->choices($options), 'feat_id' => $id, 'ability_increases' => []])
                ->assertUnprocessable()->assertJsonValidationErrors('feat_id');
        }
        $this->assertSame(10, $hero->fresh()->max_health_points);
    }

    public function test_fixed_hit_points_have_a_minimum_of_one(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero('Magicien');
        $hero->abilities()->whereHas('ability', fn ($query) => $query->where('name', 'Constitution'))->update(['score' => 1]);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $options = $this->getJson($url.'/options')->assertOk()->json();
        $this->assertSame(1, $options['hit_points']['gain']);
        $this->postJson($url, $this->choices($options))->assertOk();
        $this->assertSame(11, $hero->fresh()->max_health_points);
        $this->assertSame(9, $hero->fresh()->current_health_points);
    }

    public function test_constitution_increase_respects_the_floor_on_each_previous_level(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        foreach ([1 => 5, 3 => 6, 5 => 10] as $score => $expectedHp) {
            $hero = $this->hero('Magicien');
            $modifier = (int) floor(($score - 10) / 2);
            $hp = max(1, 6 + $modifier) + 2 * max(1, 4 + $modifier);
            $hero->update(['max_health_points' => $hp, 'current_health_points' => $hp - 1]);
            $hero->levels()->create(['character_level' => 3, 'experience_points' => 0]);
            $hero->abilities()->whereHas('ability', fn ($query) => $query->where('abbreviation', 'CON'))->update(['score' => $score]);
            $this->actingAs($hero->user);
            $url = '/api/characters/'.$hero->id.'/level-up';
            $options = $this->getJson($url.'/options')->assertOk()->json();
            $this->postJson($url, $this->choices($options))->assertOk();
            $this->assertSame($expectedHp, $hero->fresh()->max_health_points);
            $this->assertSame($expectedHp - 1, $hero->fresh()->current_health_points);
        }
    }

    public function test_tool_choice_uses_its_relation_after_names_change(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero();
        $hero->levels()->create(['character_level' => 2, 'experience_points' => 0]);
        $option = FeatureOption::whereNotNull('tool_id')->firstOrFail();
        $option->update(['name' => 'Libellé indépendant']);
        $option->feature->update(['name' => 'Capacité renommée']);
        $this->actingAs($hero->user);
        $url = '/api/characters/'.$hero->id.'/level-up';
        $options = $this->getJson($url.'/options')->assertOk()->json();
        $payload = $this->choices($options);
        foreach ($payload['feature_choices'] as &$choice) {
            if ($choice['feature_id'] === $option->feature_id) {
                $choice['option_ids'] = [$option->id];
            }
        }
        unset($choice);
        $this->postJson($url, $payload)->assertOk();
        $this->assertDatabaseHas('character_tools', ['character_id' => $hero->id, 'tool_id' => $option->tool_id]);
    }

    public function test_seeders_are_repeatable_and_preserve_characters(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $hero = $this->hero('Magicien');
        $tables = ['characters', 'character_classes', 'features', 'feature_options', 'feats', 'spells', 'class_spells', 'subclasses', 'subclass_levels', 'subclass_features', 'subclass_level_spells'];
        $before = collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()]);
        $this->seed(RulesetDatabaseSeeder::class);
        $this->seed(RulesetDatabaseSeeder::class);
        $this->assertEquals($before, collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()]));
        $this->assertSame($hero->user_id, $hero->fresh()->user_id);
        $this->assertSame(10, $hero->fresh()->max_health_points);
        foreach (Spell::all() as $spell) {
            $this->assertNotEmpty($spell->description, $spell->name);
        }
        $wizard = CharacterClass::where('name', 'Magicien')->firstOrFail();
        $this->assertSame(2, $wizard->subclasses()->first()->classLevel->level->level_number);
    }

    public function test_spell_summaries_are_present_and_preserved_by_seeders_and_level_up_options(): void
    {
        $source = json_decode(file_get_contents(base_path('tests/Fixtures/srd-spell-descriptions.json')), true, flags: JSON_THROW_ON_ERROR);
        $catalog = collect(json_decode(file_get_contents(database_path('seeders/ruleset/data/spells-2014.json')), true, flags: JSON_THROW_ON_ERROR))->keyBy('index');
        $this->assertCount(139, $source['spells']);
        $this->assertEqualsCanonicalizing(array_column($source['spells'], 'index'), $catalog->keys()->all());
        $this->seed(RulesetDatabaseSeeder::class);
        $stored = Spell::all()->keyBy('name');

        foreach ($source['spells'] as $spell) {
            $imported = $catalog[$spell['index']];
            $this->assertIsString($imported['description'], $spell['index']);
            $this->assertNotSame('', trim($imported['description']), $spell['index']);
            $this->assertSame($imported['description'], $stored[$imported['name']]->description, $imported['name']);
        }

        $catalogByName = $catalog->keyBy('name');
        foreach (['Magicien', 'Clerc'] as $className) {
            $hero = $this->hero($className);
            $this->actingAs($hero->user);
            $options = $this->getJson('/api/characters/'.$hero->id.'/level-up/options')->assertOk()->json();
            $spells = [...$options['available_options']['spells'], ...$options['granted_spells']];
            $this->assertNotEmpty($spells);
            foreach ($spells as $spell) {
                $this->assertSame($catalogByName[$spell['name']]['description'], $spell['description'], $spell['name']);
            }
        }
    }
}
