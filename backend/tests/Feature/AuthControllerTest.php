<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * L'auth est en mode "stateful SPA" (cookie/session) : Sanctum ne démarre
     * la session que si la requête vient d'un domaine listé dans
     * SANCTUM_STATEFUL_DOMAINS. Sans ce header, $request->session() plante.
     */
    private const ORIGIN_HEADERS = ['Origin' => 'http://localhost:3000'];

    protected function createUser(string $password = 'password123', array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'john@example.com',
            'password' => Hash::make($password),
        ], $overrides));
    }

    // -----------------------------
    // POST /api/register
    // -----------------------------

    public function test_register_reussit_avec_des_donnees_valides(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Registered successfully',
            ])
            ->assertJsonPath('user.email', 'new@example.com')
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertTrue(Hash::check('password123', User::where('email', 'new@example.com')->first()->password));
    }

    public function test_register_authentifie_automatiquement_l_utilisateur(): void
    {
        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], self::ORIGIN_HEADERS);

        $this->assertAuthenticated();
        $this->assertSame('new@example.com', auth()->user()->email);
    }

    public function test_register_echoue_avec_un_payload_vide_et_retourne_toutes_les_erreurs(): void
    {
        $response = $this->postJson('/api/register', [], self::ORIGIN_HEADERS);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_echoue_si_email_mal_formate(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'pas-un-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_echoue_si_email_deja_utilise(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/register', [
            'name' => 'Autre Nom',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_echoue_si_password_trop_court(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'new@example.com',
            'password' => 'court1',
            'password_confirmation' => 'court1',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_register_echoue_si_confirmation_ne_correspond_pas(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'autre-mdp',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    // -----------------------------
    // POST /api/login
    // -----------------------------

    public function test_login_reussit_avec_des_identifiants_valides(): void
    {
        $this->createUser('password123');

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(200)->assertJson(['message' => 'Connexion réussie']);
        $this->assertAuthenticated();
    }

    public function test_login_echoue_avec_un_mauvais_mot_de_passe(): void
    {
        $this->createUser('password123');

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'mauvais-mot-de-passe',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(401)->assertJson(['message' => 'Les identifiants fournis sont invalides']);
        $this->assertGuest();
    }

    public function test_login_echoue_avec_un_email_inexistant(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'inexistant@example.com',
            'password' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(401)->assertJson(['message' => 'Les identifiants fournis sont invalides']);
        $this->assertGuest();
    }

    public function test_login_echoue_si_email_manquant(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_login_echoue_si_password_manquant(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_login_echoue_si_email_mal_formate(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'pas-un-email',
            'password' => 'password123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // -----------------------------
    // POST /api/logout
    // -----------------------------

    /**
     * Extrait les cookies d'une réponse pour les rejouer sur la requête suivante.
     * Nécessaire ici car chaque appel HTTP de test est isolé : sans ça, la
     * session ouverte par /api/login ne serait pas vue par /api/logout.
     */
    protected function withCookiesFrom($response): static
    {
        $cookies = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        return $this->withCookies($cookies);
    }

    public function test_logout_reussit_pour_un_utilisateur_connecte(): void
    {
        $this->createUser('password123');

        $login = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ], self::ORIGIN_HEADERS)->assertStatus(200);

        $response = $this->withCookiesFrom($login)->postJson('/api/logout', [], self::ORIGIN_HEADERS);

        $response->assertStatus(200)->assertJson(['message' => 'Déconnexion réussie']);
    }

    public function test_logout_echoue_pour_un_utilisateur_non_authentifie(): void
    {
        $response = $this->postJson('/api/logout', [], self::ORIGIN_HEADERS);

        $response->assertStatus(401);
    }

    // -----------------------------
    // GET /api/user
    // -----------------------------

    public function test_utilisateur_authentifie_peut_acceder_a_slash_user(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->getJson('/api/user', self::ORIGIN_HEADERS);

        $response->assertStatus(200)
            ->assertJson(['id' => $user->id, 'email' => $user->email])
            ->assertJsonMissingPath('password');
    }

    public function test_utilisateur_non_authentifie_ne_peut_pas_acceder_a_slash_user(): void
    {
        $response = $this->getJson('/api/user', self::ORIGIN_HEADERS);

        $response->assertStatus(401);
    }

    // -----------------------------
    // POST /api/updateProfile
    // -----------------------------

    public function test_utilisateur_peut_modifier_son_nom(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'name' => 'Nouveau Nom',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(200)->assertJson([
            'message' => 'Profil mis à jour avec succès',
            'user' => ['name' => 'Nouveau Nom'],
        ]);
        $this->assertSame('Nouveau Nom', $user->fresh()->name);
    }

    public function test_modification_du_nom_echoue_si_trop_long(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'name' => str_repeat('a', 256),
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_utilisateur_peut_modifier_son_mot_de_passe(): void
    {
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'current_password' => 'ancien-mdp123',
            'password' => 'nouveau-mdp123',
            'password_confirmation' => 'nouveau-mdp123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(200)->assertJson(['message' => 'Profil mis à jour avec succès']);
        $this->assertTrue(Hash::check('nouveau-mdp123', $user->fresh()->password));
    }

    public function test_utilisateur_peut_modifier_nom_et_mot_de_passe_en_meme_temps(): void
    {
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'name' => 'Nouveau Nom',
            'current_password' => 'ancien-mdp123',
            'password' => 'nouveau-mdp123',
            'password_confirmation' => 'nouveau-mdp123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(200);

        $fresh = $user->fresh();
        $this->assertSame('Nouveau Nom', $fresh->name);
        $this->assertTrue(Hash::check('nouveau-mdp123', $fresh->password));
    }

    public function test_modification_du_mot_de_passe_echoue_si_current_password_incorrect(): void
    {
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'current_password' => 'mauvais-mdp',
            'password' => 'nouveau-mdp123',
            'password_confirmation' => 'nouveau-mdp123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJson(['message' => 'Le mot de passe actuel est incorrect']);
        $this->assertTrue(Hash::check('ancien-mdp123', $user->fresh()->password));
    }

    public function test_modification_du_mot_de_passe_echoue_si_current_password_manquant(): void
    {
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'password' => 'nouveau-mdp123',
            'password_confirmation' => 'nouveau-mdp123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['current_password']);
        $this->assertTrue(Hash::check('ancien-mdp123', $user->fresh()->password));
    }

    public function test_modification_du_mot_de_passe_echoue_si_confirmation_ne_correspond_pas(): void
    {
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'current_password' => 'ancien-mdp123',
            'password' => 'nouveau-mdp123',
            'password_confirmation' => 'autre-mdp',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_modification_du_mot_de_passe_echoue_si_trop_court(): void
    {
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'current_password' => 'ancien-mdp123',
            'password' => 'court',
            'password_confirmation' => 'court',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_modification_du_profil_echoue_si_aucune_donnee_envoyee(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [], self::ORIGIN_HEADERS);

        $response->assertStatus(422)->assertJson(['message' => 'Aucune donnée à mettre à jour']);
    }

    public function test_envoyer_current_password_seul_ne_modifie_rien_mais_reussit(): void
    {
        // Comportement actuel du contrôleur : "current_password" seul est une
        // donnée "présente" au sens de la validation, donc la garde
        // "aucune donnée envoyée" ne se déclenche pas, mais ni le nom ni le
        // mot de passe ne sont touchés puisque "password" est absent.
        $user = $this->createUser('ancien-mdp123');
        $this->actingAs($user);

        $response = $this->postJson('/api/updateProfile', [
            'current_password' => 'ancien-mdp123',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(200)->assertJson(['message' => 'Profil mis à jour avec succès']);
        $this->assertSame($user->name, $user->fresh()->name);
        $this->assertTrue(Hash::check('ancien-mdp123', $user->fresh()->password));
    }

    public function test_utilisateur_non_authentifie_ne_peut_pas_modifier_le_profil(): void
    {
        $response = $this->postJson('/api/updateProfile', [
            'name' => 'Nouveau Nom',
        ], self::ORIGIN_HEADERS);

        $response->assertStatus(401);
    }
}
