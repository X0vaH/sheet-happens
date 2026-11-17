<?php

namespace Tests\Feature;

use App\Models\ruleset\Alignment;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassProficiency;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Language;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Species;
use App\Models\ruleset\SubclassLevel;
use Database\Seeders\ruleset\AbilitySeeder;
use Database\Seeders\ruleset\ClassSeeder;
use Database\Seeders\ruleset\FeatureSeeder;
use Database\Seeders\ruleset\RulesetDatabaseSeeder;
use Database\Seeders\ruleset\RulesetSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RulesetSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_relancer_le_seeder_ne_cree_aucun_doublon(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $counts = $this->rowCounts();

        $this->seed(RulesetDatabaseSeeder::class);

        $this->assertSame($counts, $this->rowCounts());
    }

    public function test_reseed_updates_descriptions_and_moves_only_the_unlock_feature(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);
        $wizard = CharacterClass::where('name', 'Magicien')->firstOrFail();
        $description = $wizard->description;
        $wizard->update(['description' => 'Ancienne description']);
        $subclass = $wizard->subclasses()->firstOrFail();
        $oldClassLevel = $wizard->classLevels()->whereHas('level', fn ($query) => $query->where('level_number', 3))->firstOrFail();
        $subclass->update(['class_level_id' => $oldClassLevel->id]);
        $previous = SubclassLevel::firstOrCreate(['subclass_id' => $subclass->id, 'level_id' => $oldClassLevel->level_id]);
        $unlock = Feature::where('name', 'Savant en évocation')->firstOrFail();
        $other = Feature::factory()->create(['ruleset_id' => $wizard->ruleset_id, 'name' => 'Capacité indépendante']);
        $previous->features()->syncWithoutDetaching([$unlock->id, $other->id]);
        $frenzy = Feature::where('name', 'Frénésie')->firstOrFail();
        $frenzy->update(['name' => 'Fureur implacable', 'description' => 'Ancienne description']);

        $this->seed(RulesetDatabaseSeeder::class);

        $this->assertSame($description, $wizard->fresh()->description);
        $this->assertSame('Frénésie', $frenzy->fresh()->name);
        $this->assertNotSame('Ancienne description', $frenzy->fresh()->description);
        $this->assertSame(2, $subclass->fresh()->classLevel->level->level_number);
        $this->assertEquals([$other->id], $previous->features()->pluck('features.id')->all());
        $this->assertSame($oldClassLevel->level_id, $previous->fresh()->level_id);
    }

    public function test_le_seeder_cible_son_ruleset_meme_en_presence_d_un_autre(): void
    {
        Ruleset::factory()->create(['name' => 'Autre ruleset', 'version' => '1']);

        $this->seed(RulesetDatabaseSeeder::class);

        $other = Ruleset::where('name', 'Autre ruleset')->firstOrFail();
        $this->assertSame(0, $other->abilities()->count());
    }

    public function test_le_seeder_cree_les_neuf_alignements_avec_description(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $alignments = Alignment::query()->get();

        $this->assertCount(9, $alignments);
        $this->assertSame(0, $alignments->filter(fn ($a) => blank($a->description))->count());
    }

    public function test_le_seeder_cree_les_seize_langues_avec_description(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $languages = Language::query()->get();

        $this->assertCount(16, $languages);
        $this->assertSame(0, $languages->filter(fn ($l) => blank($l->description))->count());
    }

    public function test_le_seeder_cree_les_maitrises_des_cinq_classes(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $counts = ClassProficiency::query()
            ->join('classes', 'classes.id', '=', 'class_proficiencies.class_id')
            ->selectRaw('classes.name as class_name, type, count(*) as total')
            ->groupBy('classes.name', 'type')
            ->get()
            ->groupBy('class_name');

        $this->assertEqualsCanonicalizing(
            ['Barbare', 'Clerc', 'Guerrier', 'Magicien', 'Roublard'],
            $counts->keys()->all(),
        );
        $this->assertNull($counts['Magicien']->firstWhere('type', 'armor'), 'Le Magicien n’a aucune maîtrise d’armure.');
        $this->assertSame(5, (int) $counts['Magicien']->firstWhere('type', 'weapon')->total);

        foreach (['Barbare', 'Clerc', 'Guerrier', 'Roublard'] as $class) {
            $this->assertNotNull($counts[$class]->firstWhere('type', 'armor'), "$class devrait avoir une maîtrise d’armure.");
            $this->assertNotNull($counts[$class]->firstWhere('type', 'weapon'), "$class devrait avoir une maîtrise d’arme.");
        }
    }

    public function test_le_style_de_combat_a_ses_six_options(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $feature = Feature::query()->where('name', 'Style de combat')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            ['Archerie', 'Défense', 'Duel', 'Combat à deux armes', 'Combat avec arme à deux mains', 'Protection'],
            $feature->options()->pluck('name')->all(),
        );
    }

    public function test_les_especes_ont_leurs_langues_fixes(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $elfe = Species::query()->where('name', 'Elfe')->firstOrFail();
        $humain = Species::query()->where('name', 'Humain')->firstOrFail();

        $this->assertEqualsCanonicalizing(['Commun', 'Elfique'], $elfe->languages()->pluck('name')->all());
        $this->assertEqualsCanonicalizing(['Commun'], $humain->languages()->pluck('name')->all());
    }

    public function test_les_historiques_ont_leur_nombre_de_langues_au_choix(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $this->assertSame(2, Background::query()->where('name', 'Acolyte')->value('language_choice_count'));
        $this->assertSame(0, Background::query()->where('name', 'Soldat')->value('language_choice_count'));
        $this->assertSame(1, Background::query()->where('name', 'Guide')->value('language_choice_count'));
    }

    public function test_les_classes_ont_leurs_nombres_de_choix(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $magicien = CharacterClass::query()->where('name', 'Magicien')->firstOrFail();
        $guerrier = CharacterClass::query()->where('name', 'Guerrier')->firstOrFail();

        $this->assertSame(2, $guerrier->skill_choice_count);
        $this->assertSame(0, $guerrier->cantrip_choice_count);
        $this->assertSame(0, $guerrier->spell_choice_count);
        $this->assertSame(2, $magicien->skill_choice_count);
        $this->assertSame(3, $magicien->cantrip_choice_count);
        $this->assertSame(6, $magicien->spell_choice_count);
    }

    public function test_le_nombre_de_sorts_seedes_couvre_les_limites_de_choix(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        foreach (['Clerc', 'Magicien'] as $className) {
            $class = CharacterClass::query()->where('name', $className)->firstOrFail();

            $cantripsAvailable = $class->spells()->where('spell_level', 0)->count();
            $spellsAvailable = $class->spells()->where('spell_level', 1)->count();

            // Strict (pas juste >=) : il ne suffit pas que le compte tombe juste, sinon le
            // joueur n'a aucun vrai choix (il doit tout cocher).
            $this->assertGreaterThan(
                $class->cantrip_choice_count,
                $cantripsAvailable,
                "{$className} : {$cantripsAvailable} sorts mineurs seedés pour {$class->cantrip_choice_count} requis, pas de marge de choix.",
            );
            $this->assertGreaterThan(
                $class->spell_choice_count,
                $spellsAvailable,
                "{$className} : {$spellsAvailable} sorts niveau 1 seedés pour {$class->spell_choice_count} requis, pas de marge de choix.",
            );
        }
    }

    public function test_le_guerrier_a_ses_quatre_groupes_d_equipement_de_depart(): void
    {
        $this->seed(RulesetDatabaseSeeder::class);

        $guerrier = CharacterClass::query()->where('name', 'Guerrier')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            ['Armure', 'Arme principale', 'Arme secondaire', 'Équipement d\'aventurier'],
            $guerrier->startingEquipmentGroups()->pluck('label')->all(),
        );

        $armure = $guerrier->startingEquipmentGroups()->where('label', 'Armure')->firstOrFail();
        $this->assertCount(2, $armure->options);
    }

    public function test_les_seeders_de_classes_et_d_aptitudes_ne_font_pas_une_requete_par_ligne(): void
    {
        $this->seed([RulesetSeeder::class, AbilitySeeder::class]);

        DB::enableQueryLog();
        $this->seed(FeatureSeeder::class);
        $featureQueries = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->seed(ClassSeeder::class);
        $classQueries = count(DB::getQueryLog());

        // Plafonds larges (mesures actuelles : ~4 et ~120) pour détecter un retour au N+1 (~100 et ~580).
        $this->assertLessThan(20, $featureQueries);
        $this->assertLessThan(250, $classQueries);
    }

    /**
     * @return array<string, int>
     */
    private function rowCounts(): array
    {
        return collect(DB::select("select name from sqlite_master where type = 'table' and name not like 'sqlite_%' and name != 'migrations'"))
            ->mapWithKeys(fn ($table) => [$table->name => DB::table($table->name)->count()])
            ->all();
    }
}
