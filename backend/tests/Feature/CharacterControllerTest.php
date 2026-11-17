<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterFeature;
use App\Models\CharacterFeatureChoice;
use App\Models\CharacterSkillProficiency;
use App\Models\FeatureOption;
use App\Models\ruleset\Ability;
use App\Models\ruleset\Alignment;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Item;
use App\Models\ruleset\Language;
use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Species;
use App\Models\ruleset\StartingEquipmentGroup;
use App\Models\ruleset\StartingEquipmentOption;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCharacterWithStats;
use Tests\TestCase;

class CharacterControllerTest extends TestCase
{
    use CreatesCharacterWithStats;
    use RefreshDatabase;

    /**
     * L'auth est en mode "stateful SPA" (cookie/session) : Sanctum ne démarre
     * la session que si la requête vient d'un domaine listé dans
     * SANCTUM_STATEFUL_DOMAINS. Sans ce header, $request->session() plante.
     */
    private const ORIGIN_HEADERS = ['Origin' => 'http://localhost:3000'];

    // -----------------------------
    // GET /api/characters
    // -----------------------------

    public function test_utilisateur_authentifie_peut_lister_ses_personnages(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $characters = Character::factory()->count(2)->for($user)->create();

        $response = $this->getJson('/api/characters', self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'characters');
        $response->assertJsonPath('characters.0.id', $characters[0]->id);
        $response->assertJsonPath('characters.1.id', $characters[1]->id);
    }

    public function test_la_liste_ne_contient_pas_les_personnages_d_un_autre_utilisateur(): void
    {
        $user = User::factory()->create();
        $autreUtilisateur = User::factory()->create();
        $this->actingAs($user);

        Character::factory()->for($user)->create();
        Character::factory()->for($autreUtilisateur)->create();

        $response = $this->getJson('/api/characters', self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'characters');
    }

    public function test_la_liste_est_vide_quand_l_utilisateur_n_a_aucun_personnage(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/characters', self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'characters');
    }

    public function test_utilisateur_non_authentifie_ne_peut_pas_lister_les_personnages(): void
    {
        $response = $this->getJson('/api/characters', self::ORIGIN_HEADERS);

        $response->assertStatus(401);
    }

    // -----------------------------
    // GET /api/characters/{character}
    // -----------------------------

    public function test_utilisateur_authentifie_peut_voir_le_detail_de_son_personnage(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = Character::factory()->for($user)->create();

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.id', $character->id);
    }

    public function test_detail_inclut_les_sauvegardes_et_options_de_capacites(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create();
        $class = ClassDefinition::factory()->create();
        $ability = Ability::factory()->create();
        $class->savingThrows()->attach($ability);
        $character->classes()->create(['class_id' => $class->id, 'first_taken_at_character_level' => 1]);
        $feature = CharacterFeature::factory()->create(['character_id' => $character->id]);
        $option = FeatureOption::factory()->create(['feature_id' => $feature->feature_id]);
        $feature->choices()->create(['feature_option_id' => $option->id]);

        $this->actingAs($user)->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS)
            ->assertOk()
            ->assertJsonPath('character.classes.0.class_definition.saving_throws.0.id', $ability->id)
            ->assertJsonPath('character.features.0.choices.0.feature_option.id', $option->id);
    }

    public function test_utilisateur_ne_peut_pas_voir_le_detail_du_personnage_d_un_autre_utilisateur(): void
    {
        $user = User::factory()->create();
        $autreUtilisateur = User::factory()->create();
        $this->actingAs($user);

        $character = Character::factory()->for($autreUtilisateur)->create();

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(404);
    }

    public function test_le_detail_d_un_personnage_inexistant_retourne_404(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/characters/999999', self::ORIGIN_HEADERS);

        $response->assertStatus(404);
    }

    public function test_utilisateur_non_authentifie_ne_peut_pas_voir_le_detail_d_un_personnage(): void
    {
        $character = Character::factory()->create();

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(401);
    }

    // -----------------------------
    // DELETE /api/characters/{character}
    // -----------------------------

    public function test_utilisateur_peut_supprimer_son_personnage(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = Character::factory()->for($user)->create();
        $autrePersonnage = Character::factory()->for($user)->create();

        $response = $this->deleteJson("/api/characters/{$character->id}", [], self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Personnage supprimé avec succès');
        $this->assertDatabaseMissing('characters', ['id' => $character->id]);
        $this->assertDatabaseHas('characters', ['id' => $autrePersonnage->id]);
    }

    public function test_la_suppression_d_un_personnage_supprime_ses_donnees_rattachees(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithStats(user: $user);
        $autrePersonnage = $this->characterWithStats(user: $user);

        $this->assertDatabaseHas('character_abilities', ['character_id' => $character->id]);
        $this->assertDatabaseHas('character_classes', ['character_id' => $character->id]);
        $this->assertDatabaseHas('character_levels', ['character_id' => $character->id]);

        $this->deleteJson("/api/characters/{$character->id}", [], self::ORIGIN_HEADERS)->assertStatus(200);

        $this->assertDatabaseMissing('character_abilities', ['character_id' => $character->id]);
        $this->assertDatabaseMissing('character_classes', ['character_id' => $character->id]);
        $this->assertDatabaseMissing('character_levels', ['character_id' => $character->id]);
        $this->assertDatabaseHas('character_abilities', ['character_id' => $autrePersonnage->id]);
        $this->assertDatabaseHas('character_classes', ['character_id' => $autrePersonnage->id]);
        $this->assertDatabaseHas('character_levels', ['character_id' => $autrePersonnage->id]);
    }

    public function test_utilisateur_ne_peut_pas_supprimer_le_personnage_d_un_autre_utilisateur(): void
    {
        $user = User::factory()->create();
        $autreUtilisateur = User::factory()->create();
        $this->actingAs($user);

        $character = Character::factory()->for($autreUtilisateur)->create();

        $response = $this->deleteJson("/api/characters/{$character->id}", [], self::ORIGIN_HEADERS);

        $response->assertStatus(404);
        $this->assertDatabaseHas('characters', ['id' => $character->id]);
    }

    public function test_la_suppression_d_un_personnage_inexistant_retourne_404(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->deleteJson('/api/characters/999999', [], self::ORIGIN_HEADERS);

        $response->assertStatus(404);
    }

    public function test_utilisateur_non_authentifie_ne_peut_pas_supprimer_un_personnage(): void
    {
        $character = Character::factory()->create();

        $response = $this->deleteJson("/api/characters/{$character->id}", [], self::ORIGIN_HEADERS);

        $response->assertStatus(401);
        $this->assertDatabaseHas('characters', ['id' => $character->id]);
    }

    public function test_la_liste_est_triee_par_order_index_et_renvoie_le_prochain_niveau(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $second = $this->characterWithStats(level: 5, user: $user);
        $second->update(['order_index' => 3]);
        $first = $this->characterWithStats(level: 2, user: $user);
        $first->update(['order_index' => 1]);

        $response = $this->getJson('/api/characters', self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('characters.0.id', $first->id);
        $response->assertJsonPath('characters.0.order_index', 1);
        $response->assertJsonPath('characters.0.next_level', 3);
        $response->assertJsonPath('characters.1.id', $second->id);
        $response->assertJsonPath('characters.1.order_index', 3);
        $response->assertJsonPath('characters.1.next_level', null);
    }

    public function test_le_detail_renvoie_les_jets_de_sauvegarde_de_la_classe(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithStats(['Force' => 14, 'Dextérité' => 8], user: $user);
        $force = Ability::query()->where('ruleset_id', $character->ruleset_id)->where('name', 'Force')->firstOrFail();
        $character->classes()->first()->classDefinition->savingThrows()->attach($force);

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.classes.0.class_definition.saving_throws.0.name', 'Force');
        $response->assertJsonPath('character.saving_throws.0', ['ability' => 'Force', 'proficient' => true, 'bonus' => 4]);
        $response->assertJsonPath('character.saving_throws.1', ['ability' => 'Dextérité', 'proficient' => false, 'bonus' => -1]);
    }

    public function test_le_detail_renvoie_les_armes_de_l_inventaire_avec_un_bonus_d_attaque(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithStats(['Force' => 16], user: $user);
        $weapon = $this->giveWeapon($character, "Hache d'armes");

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.inventory.0.item.weapon.id', $weapon->id);
        $response->assertJsonPath('character.inventory.0.item.weapon.damage_dice', $weapon->damage_dice);
        $response->assertJsonPath('character.inventory.0.item.weapon.damage_type', $weapon->damage_type);
        $response->assertJsonPath('character.inventory.0.item.weapon.attack_bonus', 5);
    }

    public function test_le_detail_renvoie_les_competences_avec_maitrise_et_bonus(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithStats(['Force' => 14, 'Dextérité' => 12], user: $user);
        $force = Ability::query()->where('ruleset_id', $character->ruleset_id)->where('name', 'Force')->firstOrFail();
        $dexterite = Ability::query()->where('ruleset_id', $character->ruleset_id)->where('name', 'Dextérité')->firstOrFail();
        $athletisme = Skill::factory()->for($character->ruleset)->create(['name' => 'Athlétisme', 'ability_id' => $force->id]);
        $discretion = Skill::factory()->for($character->ruleset)->create(['name' => 'Discrétion', 'ability_id' => $dexterite->id]);
        $escamotage = Skill::factory()->for($character->ruleset)->create(['name' => 'Escamotage', 'ability_id' => $dexterite->id]);
        CharacterSkillProficiency::factory()->for($character)->create([
            'skill_id' => $athletisme->id,
            'proficiency_level' => 'proficient',
        ]);
        CharacterSkillProficiency::factory()->for($character)->create([
            'skill_id' => $escamotage->id,
            'proficiency_level' => 'expertise',
        ]);

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.skills', [
            ['name' => 'Athlétisme', 'ability' => 'Force', 'proficient' => true, 'bonus' => 4],
            ['name' => 'Discrétion', 'ability' => 'Dextérité', 'proficient' => false, 'bonus' => 1],
            ['name' => 'Escamotage', 'ability' => 'Dextérité', 'proficient' => true, 'bonus' => 5],
        ]);
    }

    public function test_le_detail_renvoie_les_statistiques_de_combat_calculees(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithStats(['Dextérité' => 14], level: 5, user: $user);
        $this->addAbilityIncrease($character, 'Dextérité', 2);
        $this->giveArmor($character, 'Cuir');

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.armor_class', 14);
        $response->assertJsonPath('character.initiative', 3);
        $response->assertJsonPath('character.proficiency_bonus', 3);
        $response->assertJsonPath('character.next_level', null);
        $response->assertJsonPath('character.abilities.1.ability.name', 'Dextérité');
        $response->assertJsonPath('character.abilities.1.score', 14);
        $response->assertJsonPath('character.abilities.1.effective_score', 16);
    }

    public function test_le_detail_renvoie_le_prochain_niveau_avant_le_maximum(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithStats(level: 2, user: $user);

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.next_level', 3);
    }

    /**
     * Crée un personnage ayant choisi l'option « Défense » de la capacité « Style de combat ».
     */
    private function characterWithFightingStyle(User $user): Character
    {
        $character = Character::factory()->for($user)->create();
        $feature = Feature::factory()->create(['name' => 'Style de combat']);
        $option = FeatureOption::factory()->for($feature)->create([
            'name' => 'Défense',
            'description' => '+1 CA lorsque le personnage porte une armure.',
        ]);
        $characterFeature = CharacterFeature::factory()->for($character)->create(['feature_id' => $feature->id]);
        CharacterFeatureChoice::factory()->create([
            'character_feature_id' => $characterFeature->id,
            'feature_option_id' => $option->id,
        ]);

        return $character;
    }

    public function test_la_liste_renvoie_le_style_de_combat_choisi(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->characterWithFightingStyle($user);
        Character::factory()->for($user)->create();

        $response = $this->getJson('/api/characters', self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('characters.0.fighting_style', [
            'name' => 'Défense',
            'description' => '+1 CA lorsque le personnage porte une armure.',
        ]);
        $response->assertJsonPath('characters.1.fighting_style', null);
    }

    public function test_le_detail_renvoie_le_style_de_combat_choisi(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = $this->characterWithFightingStyle($user);

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.fighting_style', [
            'name' => 'Défense',
            'description' => '+1 CA lorsque le personnage porte une armure.',
        ]);
    }

    public function test_le_detail_renvoie_un_style_de_combat_nul_sans_choix(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $character = Character::factory()->for($user)->create();

        $response = $this->getJson("/api/characters/{$character->id}", self::ORIGIN_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonPath('character.fighting_style', null);
    }

    // -----------------------------
    // POST /api/characters
    // -----------------------------

    /**
     * Construit un payload de création de personnage niveau 1 valide, avec tout le
     * catalogue dont il dépend créé au passage.
     *
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        $ruleset = Ruleset::factory()->create();

        $species = Species::factory()->for($ruleset)->create();
        $background = Background::factory()->for($ruleset)->create();
        $alignment = Alignment::factory()->for($ruleset)->create();
        $class = ClassDefinition::factory()->for($ruleset)->create(['hit_die' => 10]);
        $level = Level::factory()->for($ruleset)->create(['level_number' => 1]);
        ClassLevel::factory()->create(['class_id' => $class->id, 'level_id' => $level->id]);

        $strength = Ability::factory()->for($ruleset)->create(['name' => 'Force', 'abbreviation' => 'FOR']);
        $constitution = Ability::factory()->for($ruleset)->create(['name' => 'Constitution', 'abbreviation' => 'CON']);

        $skill = Skill::factory()->for($ruleset)->create(['ability_id' => $strength->id]);
        $language = Language::factory()->for($ruleset)->create();
        $tool = Tool::factory()->for($ruleset)->create();
        $item = Item::factory()->create();

        return [
            'name' => 'Elarion',
            'alignment' => $alignment->name,
            'notes' => 'Un aventurier prudent.',
            'species_id' => $species->id,
            'background_id' => $background->id,
            'class_id' => $class->id,
            'abilities' => [
                ['ability_id' => $strength->id, 'score' => 15],
                ['ability_id' => $constitution->id, 'score' => 14],
            ],
            'skill_proficiencies' => [
                ['skill_id' => $skill->id, 'proficiency_level' => 'proficient'],
            ],
            'languages' => [
                ['language_id' => $language->id, 'source' => 'species'],
            ],
            'tools' => [
                ['tool_id' => $tool->id, 'proficiency_level' => 'proficient'],
            ],
            'inventory' => [
                ['item_id' => $item->id, 'quantity' => 1],
            ],
        ];
    }

    public function test_creation_de_personnage_echoue_sans_authentification(): void
    {
        $response = $this->postJson('/api/characters', $this->validPayload());

        $response->assertStatus(401);
    }

    public function test_creation_de_personnage_reussit_avec_des_donnees_valides(): void
    {
        $payload = $this->validPayload();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('character.name', 'Elarion');
        // Constitution 14 => modificateur +2, dé de vie 10 => 12 PV de départ.
        $response->assertJsonPath('character.max_health_points', 12);
        $response->assertJsonPath('character.current_health_points', 12);
        $character = Character::findOrFail($response->json('character.id'));
        $this->assertSame(1, $character->levels()->count());
        $this->assertSame(1, $character->level->character_level);
        $this->assertDatabaseHas('character_level_classes', [
            'character_level_id' => $character->level->id,
            'character_class_id' => $character->classes()->firstOrFail()->id,
            'resulting_class_level' => 1,
        ]);

        $this->assertDatabaseHas('characters', [
            'user_id' => $user->id,
            'name' => 'Elarion',
        ]);
        $this->assertDatabaseHas('character_origins', [
            'specie_id' => $payload['species_id'],
            'background_id' => $payload['background_id'],
        ]);
        $this->assertDatabaseHas('character_classes', [
            'class_id' => $payload['class_id'],
            'first_taken_at_character_level' => 1,
        ]);
        $this->assertDatabaseCount('character_abilities', 2);
        $this->assertDatabaseCount('character_skill_proficiencies', 1);
        $this->assertDatabaseCount('character_inventory', 1);
    }

    public function test_creation_de_personnage_sauvegarde_l_emplacement_choisi(): void
    {
        $payload = $this->validPayload();
        $payload['order_index'] = 3;
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('character.order_index', 3);
    }

    public function test_creation_de_personnage_sans_emplacement_prend_le_premier_libre(): void
    {
        $payload = $this->validPayload();
        $user = User::factory()->create();
        Character::factory()->for($user)->create(['order_index' => 0]);
        Character::factory()->for($user)->create(['order_index' => 2]);
        $this->actingAs($user);

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('character.order_index', 1);
    }

    public function test_creation_de_personnage_echoue_si_l_emplacement_est_occupe(): void
    {
        $payload = $this->validPayload();
        $payload['order_index'] = 2;
        $user = User::factory()->create();
        Character::factory()->for($user)->create(['order_index' => 2]);
        $this->actingAs($user);

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['order_index' => 'Cet emplacement est déjà occupé par un autre personnage.']);
        $this->assertSame(1, Character::where('user_id', $user->id)->count());
    }

    public function test_l_emplacement_d_un_autre_utilisateur_reste_disponible(): void
    {
        $payload = $this->validPayload();
        $payload['order_index'] = 2;
        Character::factory()->for(User::factory()->create())->create(['order_index' => 2]);
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('character.order_index', 2);
    }

    public function test_creation_de_personnage_echoue_si_l_emplacement_est_hors_limites(): void
    {
        $payload = $this->validPayload();
        $payload['order_index'] = Character::MAX_PER_USER;
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['order_index']);
    }

    public function test_creation_de_personnage_echoue_si_l_utilisateur_a_deja_cinq_personnages(): void
    {
        $payload = $this->validPayload();
        $user = User::factory()->create();
        Character::factory()->count(Character::MAX_PER_USER)->for($user)->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Vous avez atteint la limite de 5 personnages. Supprimez-en un avant d\'en créer un nouveau.');
        $this->assertSame(5, Character::where('user_id', $user->id)->count());
    }

    public function test_la_limite_de_personnages_prime_sur_la_validation(): void
    {
        $user = User::factory()->create();
        Character::factory()->count(Character::MAX_PER_USER)->for($user)->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/characters', []);

        $response->assertStatus(403);
    }

    public function test_creation_de_personnage_reussit_avec_quatre_personnages_existants(): void
    {
        $payload = $this->validPayload();
        $user = User::factory()->create();
        Character::factory()->count(Character::MAX_PER_USER - 1)->for($user)->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $this->assertSame(5, Character::where('user_id', $user->id)->count());
    }

    public function test_la_limite_de_personnages_ne_compte_pas_ceux_d_un_autre_utilisateur(): void
    {
        $payload = $this->validPayload();
        Character::factory()->count(Character::MAX_PER_USER)->for(User::factory()->create())->create();
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
    }

    public function test_creation_de_personnage_echoue_avec_des_champs_requis_manquants(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'species_id', 'background_id', 'class_id', 'abilities']);
    }

    public function test_creation_de_personnage_echoue_avec_une_competence_en_double(): void
    {
        $payload = $this->validPayload();
        $payload['skill_proficiencies'][] = $payload['skill_proficiencies'][0];

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseCount('characters', 0);
    }

    public function test_creation_de_personnage_echoue_si_une_reference_de_catalogue_est_invalide(): void
    {
        $payload = $this->validPayload();
        $payload['species_id'] = 999999;

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['species_id']);
        $this->assertDatabaseCount('characters', 0);
    }

    public function test_creation_de_personnage_echoue_avec_un_alignement_inconnu(): void
    {
        $payload = $this->validPayload();
        $payload['alignment'] = 'Alignement inexistant';

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['alignment']);
        $response->assertJsonPath('errors.alignment.0', "Cet alignement n'existe pas.");
        $this->assertDatabaseCount('characters', 0);
    }

    public function test_creation_de_personnage_echoue_avec_un_alignement_d_un_autre_ruleset(): void
    {
        $payload = $this->validPayload();

        $otherRuleset = Ruleset::factory()->create(['name' => 'Autre ruleset', 'version' => '1.0']);
        Alignment::factory()->for($otherRuleset)->create(['name' => 'Alignement maison', 'description' => 'Test']);
        $payload['alignment'] = 'Alignement maison';

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['alignment']);
        $this->assertDatabaseCount('characters', 0);
    }

    public function test_creation_de_personnage_echoue_si_la_sous_classe_ne_correspond_pas_a_la_classe(): void
    {
        $payload = $this->validPayload();

        $otherRuleset = Ruleset::factory()->create(['name' => 'Autre ruleset', 'version' => '1.0']);
        $otherClass = ClassDefinition::factory()->for($otherRuleset)->create();
        $otherLevel = Level::factory()->for($otherRuleset)->create(['level_number' => 1]);
        $otherClassLevel = ClassLevel::factory()->create(['class_id' => $otherClass->id, 'level_id' => $otherLevel->id]);
        $unrelatedSubclass = Subclass::factory()->create(['class_level_id' => $otherClassLevel->id]);

        $payload['subclass_id'] = $unrelatedSubclass->id;

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subclass_id']);
    }

    public function test_creation_de_personnage_sauvegarde_un_choix_de_style_de_combat(): void
    {
        $payload = $this->validPayload();

        $feature = Feature::factory()->create(['name' => 'Style de combat']);
        $option = FeatureOption::factory()->for($feature)->create(['name' => 'Défense']);

        $payload['feature_choices'] = [
            ['feature_id' => $feature->id, 'feature_option_id' => $option->id],
        ];

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $characterId = $response->json('character.id');

        $this->assertDatabaseHas('character_features', [
            'character_id' => $characterId,
            'feature_id' => $feature->id,
            'acquired_at_character_level' => 1,
        ]);
        $characterFeatureId = CharacterFeature::query()
            ->where('character_id', $characterId)
            ->where('feature_id', $feature->id)
            ->value('id');
        $this->assertDatabaseHas('character_feature_choices', [
            'character_feature_id' => $characterFeatureId,
            'feature_option_id' => $option->id,
        ]);
    }

    public function test_creation_de_personnage_sauvegarde_un_choix_d_equipement_de_depart(): void
    {
        $payload = $this->validPayload();
        $class = ClassDefinition::query()->findOrFail($payload['class_id']);

        $group = StartingEquipmentGroup::factory()->for($class, 'characterClass')->create();
        $option = StartingEquipmentOption::factory()->for($group, 'group')->create();
        $armor = Item::factory()->create();
        $shield = Item::factory()->create();
        $option->items()->attach([$armor->id => ['quantity' => 1], $shield->id => ['quantity' => 1]]);

        $payload['starting_equipment_choices'] = [
            ['starting_equipment_option_id' => $option->id],
        ];

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $characterId = $response->json('character.id');

        $this->assertDatabaseHas('character_inventory', [
            'character_id' => $characterId,
            'item_id' => $armor->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('character_inventory', [
            'character_id' => $characterId,
            'item_id' => $shield->id,
            'quantity' => 1,
        ]);
    }

    public function test_creation_de_personnage_additionne_les_quantites_d_un_objet_deja_dans_l_inventaire(): void
    {
        $payload = $this->validPayload();
        $class = ClassDefinition::query()->findOrFail($payload['class_id']);
        $sharedItem = Item::query()->findOrFail($payload['inventory'][0]['item_id']);

        $group = StartingEquipmentGroup::factory()->for($class, 'characterClass')->create();
        $option = StartingEquipmentOption::factory()->for($group, 'group')->create();
        $option->items()->attach($sharedItem->id, ['quantity' => 2]);

        $payload['starting_equipment_choices'] = [
            ['starting_equipment_option_id' => $option->id],
        ];

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(201);
        $characterId = $response->json('character.id');

        // 1 (inventory) + 2 (option choisie) = 3, une seule ligne (contrainte unique
        // character_id/item_id) plutôt que deux insertions en conflit.
        $this->assertDatabaseHas('character_inventory', [
            'character_id' => $characterId,
            'item_id' => $sharedItem->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseCount('character_inventory', 1);
    }

    public function test_creation_de_personnage_echoue_si_l_option_d_equipement_ne_correspond_pas_a_la_classe(): void
    {
        $payload = $this->validPayload();

        $otherClass = ClassDefinition::factory()->create();
        $group = StartingEquipmentGroup::factory()->for($otherClass, 'characterClass')->create();
        $option = StartingEquipmentOption::factory()->for($group, 'group')->create();

        $payload['starting_equipment_choices'] = [
            ['starting_equipment_option_id' => $option->id],
        ];

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['starting_equipment_choices.0.starting_equipment_option_id']);
        $this->assertDatabaseCount('characters', 0);
    }

    public function test_creation_de_personnage_echoue_si_deux_choix_du_meme_groupe_d_equipement(): void
    {
        $payload = $this->validPayload();
        $class = ClassDefinition::query()->findOrFail($payload['class_id']);

        $group = StartingEquipmentGroup::factory()->for($class, 'characterClass')->create();
        $optionA = StartingEquipmentOption::factory()->for($group, 'group')->create();
        $optionB = StartingEquipmentOption::factory()->for($group, 'group')->create();

        $payload['starting_equipment_choices'] = [
            ['starting_equipment_option_id' => $optionA->id],
            ['starting_equipment_option_id' => $optionB->id],
        ];

        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/characters', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['starting_equipment_choices.1.starting_equipment_option_id']);
        $this->assertDatabaseCount('characters', 0);
    }
}
