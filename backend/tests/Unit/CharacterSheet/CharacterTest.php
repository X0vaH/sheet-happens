<?php

namespace Tests\Unit\CharacterSheet;

use App\Models\Character;
use App\Models\CharacterAbility;
use App\Models\CharacterAbilityIncrease;
use App\Models\CharacterClass;
use App\Models\CharacterDetail;
use App\Models\CharacterFeat;
use App\Models\CharacterFeature;
use App\Models\CharacterInventory;
use App\Models\CharacterLanguage;
use App\Models\CharacterLevel;
use App\Models\CharacterOrigin;
use App\Models\CharacterSkillProficiency;
use App\Models\CharacterSpell;
use App\Models\CharacterSpellSlot;
use App\Models\CharacterTool;
use App\Models\ruleset\Ruleset;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CharacterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creation_remplit_les_attributs_fillable(): void
    {
        $user = User::factory()->create();
        $ruleset = Ruleset::factory()->create();

        $character = Character::factory()->for($user)->for($ruleset)->create([
            'name' => 'Elarion',
            'current_health_points' => 10,
            'max_health_points' => 12,
            'alignment' => 'Chaotic Good',
        ]);

        $this->assertDatabaseHas('characters', [
            'id' => $character->id,
            'user_id' => $user->id,
            'ruleset_id' => $ruleset->id,
            'name' => 'Elarion',
            'current_health_points' => 10,
            'max_health_points' => 12,
            'alignment' => 'Chaotic Good',
        ]);
    }

    public function test_appartient_a_un_utilisateur(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create();

        $this->assertTrue($character->user->is($user));
    }

    public function test_appartient_a_un_ruleset(): void
    {
        $ruleset = Ruleset::factory()->create();
        $character = Character::factory()->for($ruleset)->create();

        $this->assertTrue($character->ruleset->is($ruleset));
    }

    public function test_a_un_detail(): void
    {
        $character = Character::factory()->create();
        CharacterDetail::factory()->for($character)->create();

        $this->assertInstanceOf(CharacterDetail::class, $character->detail);
    }

    public function test_a_une_origine(): void
    {
        $character = Character::factory()->create();
        CharacterOrigin::factory()->for($character)->create();

        $this->assertInstanceOf(CharacterOrigin::class, $character->origin);
    }

    public function test_a_plusieurs_objets_en_inventaire(): void
    {
        $character = Character::factory()->create();
        CharacterInventory::factory()->for($character)->count(2)->create();

        $this->assertCount(2, $character->inventory);
        $this->assertContainsOnlyInstancesOf(CharacterInventory::class, $character->inventory);
    }

    public function test_a_plusieurs_augmentations_de_caracteristique(): void
    {
        $character = Character::factory()->create();
        CharacterAbilityIncrease::factory()->for($character)->count(2)->create();

        $this->assertCount(2, $character->abilityIncreases);
    }

    public function test_a_plusieurs_classes(): void
    {
        $character = Character::factory()->create();
        CharacterClass::factory()->for($character)->create();

        $this->assertCount(1, $character->classes);
        $this->assertContainsOnlyInstancesOf(CharacterClass::class, $character->classes);
    }

    public function test_a_plusieurs_niveaux(): void
    {
        $character = Character::factory()->create();
        CharacterLevel::factory()->for($character)->count(2)->create();

        $this->assertCount(2, $character->levels);
    }

    public function test_a_plusieurs_caracteristiques(): void
    {
        $character = Character::factory()->create();
        CharacterAbility::factory()->for($character)->create();

        $this->assertCount(1, $character->abilities);
        $this->assertContainsOnlyInstancesOf(CharacterAbility::class, $character->abilities);
    }

    public function test_a_plusieurs_maitrises_de_competences(): void
    {
        $character = Character::factory()->create();
        CharacterSkillProficiency::factory()->for($character)->create();

        $this->assertCount(1, $character->skillProficiencies);
    }

    public function test_a_plusieurs_langues(): void
    {
        $character = Character::factory()->create();
        CharacterLanguage::factory()->for($character)->create();

        $this->assertCount(1, $character->languages);
    }

    public function test_a_plusieurs_outils(): void
    {
        $character = Character::factory()->create();
        CharacterTool::factory()->for($character)->create();

        $this->assertCount(1, $character->tools);
    }

    public function test_a_plusieurs_aptitudes_acquises(): void
    {
        $character = Character::factory()->create();
        CharacterFeature::factory()->for($character)->create();

        $this->assertCount(1, $character->features);
    }

    public function test_a_plusieurs_dons(): void
    {
        $character = Character::factory()->create();
        CharacterFeat::factory()->for($character)->create();

        $this->assertCount(1, $character->feats);
    }

    public function test_a_plusieurs_sorts(): void
    {
        $character = Character::factory()->create();
        CharacterSpell::factory()->for($character)->create();

        $this->assertCount(1, $character->spells);
    }

    public function test_a_plusieurs_emplacements_de_sorts(): void
    {
        $character = Character::factory()->create();
        // spell_level explicite : (character_id, spell_level) est unique, un tirage aléatoire
        // pourrait générer deux fois le même niveau et faire échouer le test de façon aléatoire.
        CharacterSpellSlot::factory()->for($character)->create(['spell_level' => 1]);
        CharacterSpellSlot::factory()->for($character)->create(['spell_level' => 2]);

        $this->assertCount(2, $character->spellSlots);
    }
}
