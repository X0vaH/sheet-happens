<?php

namespace Tests\Unit\Catalog;

use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Species;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\SubclassLevel;
use App\Models\ruleset\Tool;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Vérifie, pour chaque table pivot, les deux sens de la relation belongsToMany
 * ainsi que l'unicité de la paire de clés étrangères.
 */
class PivotRelationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, array{0: class-string, 1: string, 2: class-string, 3: string, 4: string}>
     */
    public static function pivots(): array
    {
        return [
            'background_abilities' => [Background::class, 'abilities', Ability::class, 'backgrounds', 'background_abilities'],
            'background_skills' => [Background::class, 'skills', Skill::class, 'backgrounds', 'background_skills'],
            'background_tools' => [Background::class, 'tools', Tool::class, 'backgrounds', 'background_tools'],
            'background_feats' => [Background::class, 'feats', Feat::class, 'backgrounds', 'background_feats'],
            'class_level_features' => [ClassLevel::class, 'features', Feature::class, 'classLevels', 'class_level_features'],
            'class_level_spells' => [ClassLevel::class, 'spells', Spell::class, 'classLevels', 'class_level_spells'],
            'class_saving_throws' => [CharacterClass::class, 'savingThrows', Ability::class, 'classes', 'class_saving_throws'],
            'class_spells' => [CharacterClass::class, 'spells', Spell::class, 'classes', 'class_spells'],
            'species_features' => [Species::class, 'features', Feature::class, 'species', 'species_features'],
            'subclass_features' => [SubclassLevel::class, 'features', Feature::class, 'subclassLevels', 'subclass_features'],
            'subclass_level_spells' => [SubclassLevel::class, 'spells', Spell::class, 'subclassLevels', 'subclass_level_spells'],
            'subclass_spells' => [Subclass::class, 'spells', Spell::class, 'subclasses', 'subclass_spells'],
        ];
    }

    #[DataProvider('pivots')]
    public function test_la_relation_fonctionne_dans_les_deux_sens(string $owner, string $relation, string $related, string $inverse, string $table): void
    {
        $ownerModel = $owner::factory()->create();
        $relatedModel = $related::factory()->create();

        $ownerModel->{$relation}()->attach($relatedModel);

        $this->assertCount(1, $ownerModel->{$relation}()->get());
        $this->assertTrue($ownerModel->{$relation}()->first()->is($relatedModel));
        $this->assertTrue($relatedModel->{$inverse}()->first()->is($ownerModel));
    }

    #[DataProvider('pivots')]
    public function test_un_lien_en_double_est_refuse_par_la_base(string $owner, string $relation, string $related, string $inverse, string $table): void
    {
        $ownerModel = $owner::factory()->create();
        $relatedModel = $related::factory()->create();

        $ownerModel->{$relation}()->attach($relatedModel);

        $this->expectException(QueryException::class);
        $ownerModel->{$relation}()->attach($relatedModel);
    }

    #[DataProvider('pivots')]
    public function test_sync_without_detaching_est_idempotent(string $owner, string $relation, string $related, string $inverse, string $table): void
    {
        $ownerModel = $owner::factory()->create();
        $relatedModel = $related::factory()->create();

        $ownerModel->{$relation}()->syncWithoutDetaching([$relatedModel->id]);
        $ownerModel->{$relation}()->syncWithoutDetaching([$relatedModel->id]);

        $this->assertDatabaseCount($table, 1);
    }
}
