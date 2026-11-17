# AGENTS.md

Sheet Happens API est le backend Laravel de Sheet Happens (création et
gestion de personnages de Donjons & Dragons 5e édition). Le frontend
React consomme cette API via Axios, authentifié par session (cookies) via
Laravel Sanctum en mode SPA — il n'y a pas de tokens Bearer à gérer.

## Commandes

```bash
composer install                        # dépendances PHP
php artisan serve                       # serveur de dev
composer run dev                        # serveur + file d'attente en parallèle
composer test                           # suite de tests PHPUnit (tests/Unit, tests/Feature)
./vendor/bin/pint                       # formate le code (PSR-12)
./vendor/bin/pint --test                # vérifie le style sans le modifier
php artisan migrate                     # applique les migrations
```

### Environnement pour `composer test` / Pint

- **PHP 8.4+** (voir `composer.json`).
- Avant `composer test` : un `.env` avec `APP_KEY` non vide. Sur un checkout
  neuf :

```bash
cp .env.example .env   # si .env absent
php artisan key:generate
composer install --no-interaction --prefer-dist   # si vendor/ absent
composer test
./vendor/bin/pint --test
```

Sans `APP_KEY`, Artisan/PHPUnit échouent en masse (« No application
encryption key ») — ce n’est pas une régression du diff. Si PHP ou Composer
manque, ou si `composer install` échoue pour cause de plateforme, le
signaler comme **problème d’exécution**, distinct des échecs de tests.

Exécuter `composer test` et `./vendor/bin/pint --test` avant de
considérer une tâche terminée.

## Langue

La documentation et les issues sont rédigées en **français**. Les noms
de variables/méthodes/classes sont en **anglais**, mais les
commentaires/PHPDoc et les messages retournés au client (réponses JSON)
sont en **français**, comme dans le code existant. Les commits sont
écrits en **anglais**.

## Où trouver les choses

- `routes/api.php` — toutes les routes de l'API (préfixées `/api`).
  L'authentification se fait via le middleware `auth:sanctum` ; les
  routes sensibles (`login`).
- `app/Http/Controllers/AuthController.php` — inscription, connexion,
  déconnexion, mise à jour de profil. Utilise la session Laravel
  (`Auth::attempt`, `$request->session()->regenerate()`), pas de tokens.
- `app/Models/User.php` — modèle utilisateur (Eloquent).
- `GET /api/rulesets/current/character-creation-options`
  (`RulesetController`) — catalogue de création consommé par le
  frontend. Contrat à respecter :
  - `options.abilities` est trié par `id` (FOR, DEX, CON, INT, SAG,
    CHA) ; le frontend indexe les caractéristiques sur cet ordre, donc
    ne pas le modifier.
  - Chaque item de `options.items` expose `armor` : `null` si ce n'est
    pas une armure, sinon `armor_category` (`light`, `medium`, `heavy`,
    `shield`), `armor_class` et `dexterity_bonus_limit` (`null` = pas
    de plafond, `0` = pas de bonus de DEX, `n` = plafonné à +n). Pour
    un bouclier, `armor_class` est un bonus de CA, pas une CA de base.
- `GET /api/characters` et `GET /api/characters/{character}`
  (`CharacterController`) — personnages de l'utilisateur. En plus des
  colonnes et relations, la réponse contient des valeurs calculées par
  `app/Services/CharacterStats.php` (le frontend ne les recalcule pas) :
  - Dans les deux routes : `order_index` (emplacement dans le
    carrousel, la liste est triée par `order_index` puis `id`),
    `next_level` (niveau suivant, `null` au niveau
    `Level::MAX_LEVEL`) et `fighting_style` (`{ name, description }`
    de l'option choisie, ou `null`).
  - Dans le détail seulement : `armor_class`, `initiative`,
    `proficiency_bonus`, `abilities[].effective_score` (score de base +
    `ability_increases`), `skills[]` (`{ name, ability, proficient,
    bonus }`), `saving_throws[]` (`{ ability, proficient, bonus }`) et
    `inventory[].item.weapon.attack_bonus`.
- `DELETE /api/characters/{character}` (`CharacterController::destroy`)
  — supprime un personnage de l'utilisateur et retourne `200` avec
  `{ "message": "Personnage supprimé avec succès" }`. Un personnage
  inexistant ou appartenant à un autre utilisateur retourne `404`
  (sans révéler son existence). Les données rattachées (origine,
  classes, niveaux, caractéristiques, inventaire, etc.) sont supprimées
  par `cascadeOnDelete` en base : ne pas ajouter de table liée à
  `characters` sans cette contrainte. `order_index` n'est pas
  recompacté après une suppression (l'ordre relatif est conservé).
- `POST /api/characters` (`CharacterController::store`,
  `CreateCharacterAction`) — création d'un personnage niveau 1. Limité à
  `Character::MAX_PER_USER` (5) personnages par utilisateur : au-delà,
  `CharacterLimitReachedException` est levée et l'API répond `403` avec
  `{ "message": "Vous avez atteint la limite de 5 personnages. ..." }`.
  La vérification est faite dans la transaction, avec un verrou sur la
  ligne de l'utilisateur, pour éviter un dépassement en cas de requêtes
  concurrentes. Un premier contrôle a lieu dans
  `StoreCharacterRequest::authorize()`, avant la validation : un
  utilisateur à la limite reçoit toujours `403`, même avec un payload
  invalide, donc un `422` signifie qu'il reste de la place.
  `order_index` (facultatif, `0` à `MAX_PER_USER - 1`) choisit
  l'emplacement du carrousel ; absent ou `null`, le premier emplacement
  libre est pris. Un emplacement déjà occupé par un personnage de
  l'utilisateur retourne `422` sur `order_index` (revérifié sous le même
  verrou que la limite).
- `bootstrap/app.php` — configuration du routing/middleware et des
  réponses d'erreur JSON uniformes pour les routes `api/*` (404, 405,
  422, 401, 400, 500 avec messages en français).
- `config/cors.php` — origines autorisées pilotées par la variable d'env
  `FRONTEND_URLS`.
- `tests/Feature/` et `tests/Unit/` — tests PHPUnit, un fichier par
  contrôleur/domaine.

## Contraintes de portée à respecter

Les limites produit (classes, races, niveau, multiclassage, etc.) sont
définies dans [`agent/scope.mdc`](agent/scope.mdc). Ne pas élargir cette
portée sans demande explicite. En cas de conflit d’exigences,
`docs/rencontres-client.md` prime sur `docs/cahier-des-charges.md`.

## Règles partagées pour agents

Les règles et skills partagés vivent dans [`agent/`](agent/) — source de
vérité. Chaque outil IA en reçoit une **copie** dans son dossier
(`.cursor/`, `.claude/`).

### Ajouter ou modifier une règle / un skill partagé

1. Éditer d’abord sous `agent/` :
   - règle : `agent/<nom>.mdc`
   - skill : `agent/<nom>/SKILL.md`
2. **Copier** le fichier modifié vers chaque outil (copie réelle, pas de
   lien symbolique) :
   - Cursor : `.cursor/rules/<nom>.mdc`, `.cursor/skills/<nom>/SKILL.md`
   - Claude : `.claude/rules/<nom>.md`, `.claude/skills/<nom>/SKILL.md`
3. Documenter l’entrée dans [`agent/README.md`](agent/README.md).

Les copies doivent rester identiques à `agent/` : ne jamais éditer une copie
seule. Seule exception : le frontmatter des règles `.claude/rules/*.md`, où
`paths:` (liste de motifs) remplace `globs:` et `alwaysApply:` de Cursor ; le
corps reste identique. Les skills **personnels / natifs** d’un outil (sans pendant sous
`agent/`) restent hors de cette procédure.

La revue vérifie que les copies correspondent à `agent/` : skill
[`agent/ide-code-review`](agent/ide-code-review/) (in-IDE) et prompt CI
[`.github/prompts/pr-review.md`](.github/prompts/pr-review.md) (bot PR).

## Documentation

- Vue d'ensemble du projet, installation, commandes, structure →
  [README.md](README.md)
- Normes de programmation, définition de « terminé », processus de PR →
  [CONTRIBUTING.md](CONTRIBUTING.md)
- Modèle de branches Git et workflow de tâche →
  [docs/git-conventions.md](docs/git-conventions.md)
- Inventaire des fichiers sous `agent/` → [agent/README.md](agent/README.md)
- Portée, exigences et risques du projet →
  [docs/cahier-des-charges.md](docs/cahier-des-charges.md)
- Décisions et retours du client au fil des rencontres (**source de
  vérité en cas de conflit avec le cahier des charges**) →
  [docs/rencontres-client.md](docs/rencontres-client.md)

## Démarrage Docker / Infra

`entrypoint.sh` est le point d’entrée de l’image construite par Sheet-Happens-Infra (`docker/api/Dockerfile`). `compose.yaml` (Sail) ne l’exécute pas.

Le script applique `set -euo pipefail`, prend `APP_KEY` depuis l’environnement puis depuis `.env` (une valeur vide, `""` ou `''` compte comme absente), attend MySQL, lance `php artisan migrate --force`, puis `php-fpm`. Il n’affiche jamais la clé. En production, une clé manquante arrête le conteneur : Sheet-Happens-Infra doit injecter une `APP_KEY` stable, sinon chaque déploiement invalide les sessions Sanctum. La génération automatique n’existe que pour `local`, `development` et `testing`.
