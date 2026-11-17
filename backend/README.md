# Sheet Happens API

Sheet Happens API est le **backend Laravel** de Sheet Happens, une
application permettant de créer, sauvegarder et gérer des personnages de
Donjons & Dragons 5e édition. Cette API gère l'authentification, la
persistance des utilisateurs et des personnages, et applique les règles
de validation côté serveur.

Le **frontend** (React + Vite) est un dépôt séparé et consomme cette API
via Axios, en authentification par session (cookies), avec
[Laravel Sanctum](https://laravel.com/docs/sanctum) en mode SPA.

## Prérequis

- PHP **8.4+** et [Composer](https://getcomposer.org/), **ou**
- [Docker](https://www.docker.com/) pour utiliser
  [Laravel Sail](https://laravel.com/docs/sail) (recommandé, déjà
  configuré dans ce dépôt via `compose.yaml`).

## Installation

Avec Sail (Docker) :

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

Sans Docker :

```bash
cp .env.example .env
composer install
php artisan key:generate
# ajuster DB_* dans .env (ex. sqlite en local), puis :
php artisan migrate
```

## Démarrage

Avec Sail, l'API est déjà servie sur `http://localhost` (port configurable
via `APP_PORT`). Sans Sail :

```bash
composer run dev   # démarre le serveur + la file d'attente en parallèle
# ou simplement :
php artisan serve
```

## Configuration

Variables d'environnement à ajuster pour connecter le frontend (`.env`) :

| Variable | Rôle |
| --- | --- |
| `APP_URL` | URL racine de l'API. |
| `DB_*` | Connexion à la base de données. |
| `FRONTEND_URLS` | Origine(s) autorisée(s) en CORS (ex. `http://localhost:3000`). |
| `SANCTUM_STATEFUL_DOMAINS` | Domaine(s) du frontend autorisés à s'authentifier par session/cookie via Sanctum. |

## Commandes disponibles

| Commande | Description |
| --- | --- |
| `php artisan serve` | Démarre le serveur de développement. |
| `composer run dev` | Démarre le serveur et la file d'attente en parallèle. |
| `composer test` | Lance la suite de tests PHPUnit (`tests/Unit`, `tests/Feature`). |
| `./vendor/bin/pint` | Formate le code selon PSR-12. |
| `./vendor/bin/pint --test` | Vérifie le style de code sans le modifier. |
| `php artisan migrate` | Applique les migrations de base de données. |

## Structure du projet

```
agent/                  Règles et skills partagés pour agents IA (source de vérité)
app/
  Http/Controllers/   Contrôleurs (ex. AuthController : register, login, logout, updateProfile ;
                      CharacterController : liste, détail, création et suppression de personnages)
  Models/              Modèles Eloquent (ex. User)
  Providers/            Fournisseurs de services (ex. règles de rate limiting dans AppServiceProvider)
routes/
  api.php               Routes de l'API (préfixées /api), middleware auth:sanctum et throttle
bootstrap/app.php       Configuration du routing, du middleware et des réponses d'erreur JSON
database/migrations/   Schéma de la base de données
tests/
  Unit/                 Tests unitaires
  Feature/              Tests d'intégration (ex. AuthControllerTest)
```

## Image Infra

Le fichier `entrypoint.sh` sert à l’image Docker du dépôt Sheet-Happens-Infra (`docker/api/Dockerfile`), pas à Sail. `compose.yaml` continue de lancer le runtime Sail habituel, sans ce script.

L’Infra injecte `APP_KEY` par variable d’environnement. Le script ne la journalise pas et, hors environnement local, ne la génère pas. `.env` n’entre pas dans l’image.

## Documentation du projet

- [docs/montee-de-niveau.md](docs/montee-de-niveau.md) — progression 2014,
  contrat API, mise à jour du catalogue et tests.
- [AGENTS.md](AGENTS.md) — repères pour les agents IA (commandes, procédure
  règles/skills sous `agent/` et copies vers les outils).
- [CONTRIBUTING.md](CONTRIBUTING.md) — mise en place, normes de
  programmation, définition de « terminé », processus de pull request.
- [docs/git-conventions.md](docs/git-conventions.md) — modèle de branches
  Git et workflow de tâche.
- [docs/cahier-des-charges.md](docs/cahier-des-charges.md) — portée,
  exigences et risques du projet.
- [docs/rencontres-client.md](docs/rencontres-client.md) — décisions et
  retours du client au fil des rencontres (source de vérité en cas de
  conflit avec le cahier des charges).

Les configs d’agents (`.cursor/`, `.claude/`) contiennent des copies de
`agent/`. Procédure d’ajout : [AGENTS.md](AGENTS.md).
