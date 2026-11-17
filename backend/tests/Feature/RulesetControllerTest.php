<?php

namespace Tests\Feature;

use App\Models\FeatureOption;
use App\Models\ruleset\Ability;
use App\Models\ruleset\Alignment;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\ClassProficiency;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Item;
use App\Models\ruleset\Language;
use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Species;
use App\Models\ruleset\StartingEquipmentGroup;
use App\Models\ruleset\StartingEquipmentOption;
use App\Models\ruleset\Tool;
use App\Models\User;
use Database\Seeders\ruleset\RulesetDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RulesetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_options_de_creation_de_personnage_necessitent_une_authentification(): void
    {
        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertStatus(401);
    }

    public function test_options_de_creation_de_personnage_retournent_le_catalogue(): void
    {
        $ruleset = Ruleset::factory()->create();
        Ability::factory()->for($ruleset)->create();
        Skill::factory()->for($ruleset)->create();
        Language::factory()->for($ruleset)->create();
        Tool::factory()->for($ruleset)->create();
        Species::factory()->for($ruleset)->create();
        Background::factory()->for($ruleset)->create();
        Feat::factory()->for($ruleset)->create();
        Item::factory()->create();

        $class = ClassDefinition::factory()->for($ruleset)->create();
        $level = Level::factory()->for($ruleset)->create(['level_number' => 1]);
        ClassLevel::factory()->create(['class_id' => $class->id, 'level_id' => $level->id]);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonStructure([
            'options' => [
                'ruleset' => ['id', 'name', 'version', 'edition'],
                'abilities',
                'skills',
                'languages',
                'alignments',
                'tools',
                'species' => [
                    ['id', 'name', 'size_category', 'base_speed', 'description', 'features', 'languages'],
                ],
                'backgrounds' => [
                    ['id', 'name', 'description', 'language_choice_count', 'abilities', 'skills', 'tools', 'feats'],
                ],
                'classes' => [
                    [
                        'id', 'name', 'hit_die', 'description',
                        'skill_choice_count', 'cantrip_choice_count', 'spell_choice_count',
                        'saving_throws', 'proficiencies', 'spells', 'level_one', 'starting_equipment_groups',
                    ],
                ],
                'feats',
                'items',
            ],
        ]);
        $response->assertJsonPath('options.ruleset.name', $ruleset->name);
        $response->assertJsonCount(1, 'options.classes');
    }

    public function test_les_options_d_une_aptitude_de_niveau_1_sont_incluses(): void
    {
        $ruleset = Ruleset::factory()->create();
        $class = ClassDefinition::factory()->for($ruleset)->create();
        $level = Level::factory()->for($ruleset)->create(['level_number' => 1]);
        $classLevel = ClassLevel::factory()->create(['class_id' => $class->id, 'level_id' => $level->id]);

        $feature = Feature::factory()->for($ruleset)->create(['name' => 'Style de combat']);
        $classLevel->features()->attach($feature);
        FeatureOption::factory()->for($feature)->create(['name' => 'Archerie']);
        FeatureOption::factory()->for($feature)->create(['name' => 'Défense']);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonPath('options.classes.0.level_one.features.0.name', 'Style de combat');
        $response->assertJsonCount(2, 'options.classes.0.level_one.features.0.options');
        $response->assertJsonPath('options.classes.0.level_one.features.0.options.*.name', ['Archerie', 'Défense']);
    }

    public function test_les_alignements_du_ruleset_sont_inclus(): void
    {
        $ruleset = Ruleset::factory()->create();
        Alignment::factory()->for($ruleset)->create([
            'name' => 'Loyal bon',
            'description' => 'Respecte les règles et cherche à faire le bien et à protéger les autres.',
        ]);
        Alignment::factory()->for($ruleset)->create([
            'name' => 'Chaotique mauvais',
            'description' => 'Agit égoïstement et violemment sans se soucier des règles ni du bien-être des autres.',
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonCount(2, 'options.alignments');
        $response->assertJsonPath('options.alignments.0.name', 'Loyal bon');
        $response->assertJsonPath(
            'options.alignments.0.description',
            'Respecte les règles et cherche à faire le bien et à protéger les autres.',
        );
    }

    public function test_les_langues_d_une_espece_sont_incluses(): void
    {
        $ruleset = Ruleset::factory()->create();
        $species = Species::factory()->for($ruleset)->create();
        $language = Language::factory()->for($ruleset)->create([
            'name' => 'Elfique',
            'description' => 'Langue des Elfes, fluide et mélodieuse, possédant une riche tradition littéraire.',
        ]);
        $species->languages()->attach($language);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonCount(1, 'options.species.0.languages');
        $response->assertJsonPath('options.species.0.languages.0.name', 'Elfique');
        $response->assertJsonPath(
            'options.species.0.languages.0.description',
            'Langue des Elfes, fluide et mélodieuse, possédant une riche tradition littéraire.',
        );
    }

    public function test_la_description_de_chaque_langue_est_incluse_dans_le_catalogue(): void
    {
        $ruleset = Ruleset::factory()->create();
        Language::factory()->for($ruleset)->create([
            'name' => 'Nain',
            'description' => 'Langue traditionnelle des Nains, au ton robuste et aux sonorités marquées.',
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonPath(
            'options.languages.0.description',
            'Langue traditionnelle des Nains, au ton robuste et aux sonorités marquées.',
        );
    }

    public function test_les_maitrises_de_chaque_classe_sont_incluses_y_compris_une_liste_vide(): void
    {
        $ruleset = Ruleset::factory()->create();
        $guerrier = ClassDefinition::factory()->for($ruleset)->create(['name' => 'Guerrier']);
        $magicien = ClassDefinition::factory()->for($ruleset)->create(['name' => 'Magicien']);

        ClassProficiency::factory()->for($guerrier, 'characterClass')->create(['type' => 'armor', 'label' => 'Toutes les armures']);
        ClassProficiency::factory()->for($guerrier, 'characterClass')->create(['type' => 'weapon', 'label' => 'Armes courantes et armes de guerre']);
        ClassProficiency::factory()->for($magicien, 'characterClass')->create(['type' => 'weapon', 'label' => 'Bâtons']);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $classes = collect($response->json('options.classes'))->keyBy('name');

        $this->assertSame(
            ['Toutes les armures', 'Armes courantes et armes de guerre'],
            $classes['Guerrier']['proficiencies'],
        );
        $this->assertSame(['Bâtons'], $classes['Magicien']['proficiencies']);
    }

    public function test_une_classe_sans_maitrise_a_une_liste_vide(): void
    {
        $ruleset = Ruleset::factory()->create();
        ClassDefinition::factory()->for($ruleset)->create();

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonPath('options.classes.0.proficiencies', []);
    }

    public function test_les_groupes_d_equipement_de_depart_sont_inclus(): void
    {
        $ruleset = Ruleset::factory()->create();
        $class = ClassDefinition::factory()->for($ruleset)->create();
        $item = Item::factory()->create();

        $group = StartingEquipmentGroup::factory()->for($class, 'characterClass')->create(['label' => 'Armure']);
        $option = StartingEquipmentOption::factory()->for($group, 'group')->create(['label' => 'Cotte de mailles']);
        $option->items()->attach($item, ['quantity' => 1]);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonPath('options.classes.0.starting_equipment_groups.0.label', 'Armure');
        $response->assertJsonPath('options.classes.0.starting_equipment_groups.0.options.0.label', 'Cotte de mailles');
        $response->assertJsonPath('options.classes.0.starting_equipment_groups.0.options.0.items.0.quantity', 1);
    }

    public function test_les_armures_exposent_leurs_statistiques_de_classe_d_armure(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $armors = collect($response->json('options.items'))->where('item_type', 'armor')->keyBy('name');

        $this->assertCount(9, $armors);
        foreach ($armors as $item) {
            $this->assertEqualsCanonicalizing(
                ['armor_category', 'armor_class', 'dexterity_bonus_limit'],
                array_keys($item['armor']),
            );
        }

        $this->assertSame(['armor_category' => 'light', 'armor_class' => 11, 'dexterity_bonus_limit' => null], $armors['Cuir']['armor']);
        $this->assertSame(['armor_category' => 'medium', 'armor_class' => 13, 'dexterity_bonus_limit' => 2], $armors['Chemise de mailles']['armor']);
        $this->assertSame(['armor_category' => 'heavy', 'armor_class' => 18, 'dexterity_bonus_limit' => 0], $armors['Harnois']['armor']);
        $this->assertSame(['armor_category' => 'shield', 'armor_class' => 2, 'dexterity_bonus_limit' => null], $armors['Bouclier']['armor']);
    }

    public function test_les_caracteristiques_sont_retournees_dans_l_ordre_des_id(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $this->assertSame(
            ['FOR', 'DEX', 'CON', 'INT', 'SAG', 'CHA'],
            collect($response->json('options.abilities'))->pluck('abbreviation')->all(),
        );
    }

    public function test_le_tri_des_caracteristiques_par_id_est_demande_a_la_base(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $this->actingAs(User::factory()->create());

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $this->getJson('/api/rulesets/current/character-creation-options')->assertOk();

        $abilityQueries = array_filter($queries, fn ($sql) => preg_match('/from [`"]abilities[`"] where [`"]abilities[`"]\.[`"]ruleset_id[`"] in/i', $sql));
        $this->assertNotEmpty($abilityQueries);
        foreach ($abilityQueries as $sql) {
            $this->assertMatchesRegularExpression('/order by [`"]id[`"] asc/i', $sql);
        }
    }

    public function test_un_item_qui_n_est_pas_une_armure_expose_armor_null(): void
    {
        $ruleset = Ruleset::factory()->create();
        Item::factory()->for($ruleset)->create(['name' => 'Sac à dos', 'item_type' => 'gear']);

        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/rulesets/current/character-creation-options');

        $response->assertOk();
        $response->assertJsonPath('options.items.0.name', 'Sac à dos');
        $response->assertJsonPath('options.items.0.armor', null);
        $this->assertArrayHasKey('armor', $response->json('options.items.0'));
    }
}
