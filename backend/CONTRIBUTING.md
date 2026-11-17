# Contribuer à Sheet Happens API

Ce guide décrit comment mettre en place un environnement de développement,
valider son travail avant de l'intégrer, et les normes de programmation
attendues sur ce projet.

## Mise en place

Avec [Laravel Sail](https://laravel.com/docs/sail) (Docker), déjà
configuré dans ce dépôt (`compose.yaml`) :

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

Sans Docker, avec PHP 8.4+ et Composer installés localement :

```bash
cp .env.example .env
composer install
php artisan key:generate
# ajuster DB_* dans .env pour une base locale (ex. sqlite), puis :
php artisan migrate
composer run dev   # ou : php artisan serve
```

Le frontend (dépôt React séparé) doit pointer sa variable `VITE_API_URL`
vers l'URL de cette API. Ajuster `FRONTEND_URLS` et
`SANCTUM_STATEFUL_DOMAINS` dans `.env` pour que le CORS et
l'authentification par session (Sanctum) acceptent l'origine du
frontend.

### Règles et skills IA

Les règles et skills IA partagés sont dans `agent/` (source de vérité) ;
chaque outil (`.cursor/`, `.claude/`) en reçoit une **copie** identique.
Procédure d’ajout : [AGENTS.md](AGENTS.md).

## Commandes à exécuter avant d'ouvrir une pull request

```bash
composer test                          # suite de tests PHPUnit (tests/Unit, tests/Feature)
./vendor/bin/pint --test               # vérifie le style de code sans le modifier
./vendor/bin/pint                      # corrige automatiquement le style de code
```

`composer test` et `./vendor/bin/pint --test` doivent passer sans erreur
avant d'ouvrir une pull request. Si des règles ou skills partagés sous
`agent/` changent, leurs copies dans `.cursor/` et `.claude/` doivent être
mises à jour ; la revue PR automatisée le vérifie (voir
[AGENTS.md](AGENTS.md)).

## Normes de programmation

- Noms de variables, méthodes, classes et contrôleurs clairs et
  descriptifs.
- Respecter les conventions de nommage PHP/Laravel (classes en
  PascalCase, méthodes en camelCase, migrations horodatées, etc.).
- Éviter la duplication de code : privilégier la réutilisation via les
  classes du domaine (`app/Models/`, `app/Http/Controllers/`) plutôt que
  de dupliquer de la logique entre contrôleurs.
- Une méthode devrait avoir une responsabilité claire et rester
  suffisamment courte pour être facilement compréhensible.
- Valider les données provenant du client (`Validator` /
  `$request->validate()`) avant de les traiter ou de les enregistrer.
- Utiliser des constantes plutôt que des valeurs magiques lorsque cela
  améliore la lisibilité.
- Le style de code suit **PSR-12**, appliqué via
  [Laravel Pint](https://laravel.com/docs/pint) (`vendor/bin/pint`) —
  corriger tout avertissement avant de considérer une fonctionnalité
  comme terminée.
- Documenter les méthodes avec des commentaires PHPDoc lorsque leur
  comportement n'est pas évident à la seule lecture du code (voir les
  méthodes existantes de `AuthController` pour le style attendu).

## Définition de « terminé »

Une fonctionnalité est considérée comme terminée lorsque :

- le développement est complété et fonctionne conformément au [cahier des
  charges](docs/cahier-des-charges.md) et aux [décisions du
  client](docs/rencontres-client.md) ;
- le code respecte les normes de programmation ci-dessus ;
- les validations nécessaires sont présentes (ex. empêcher les choix de
  personnage invalides, refuser l'accès aux ressources d'un autre
  utilisateur) ;
- la fonctionnalité a été testée avec des cas normaux et des cas limites
  (`composer test`) ;
- aucun problème critique connu ne subsiste ;
- l'intégration dans `develop` ne provoque pas de régression ;
- la documentation pertinente a été mise à jour.

## Branches et pull requests

Voir [docs/git-conventions.md](docs/git-conventions.md) pour le modèle de
branches complet (git-flow), le workflow de tâche et les conditions de
fusion.

En résumé :
- développer dans une branche `feature/{tacheId}-nom-branche` ou
  `bugfix/...`, jamais directement dans `develop` ou `main` ;
- ouvrir la pull request vers `develop` une fois `composer test` et
  `vendor/bin/pint --test` validés localement (et copies
  `.cursor/` / `.claude/` à jour si `agent/` a changé) ;
- la fusion requiert la validation d'un agent **et** celle d'un humain.

## Documentation

Toute modification importante du fonctionnement de l'application doit
être reflétée dans la documentation correspondante (README, ce fichier,
ou les documents sous `docs/`).

## Langue

- La documentation et les issues sont rédigées en **français**, avec une
  terminologie uniforme.
- Les noms de variables, méthodes et classes sont en **anglais**, en
  suivant les conventions PHP/Laravel.
- Les commits suivent le style déjà en usage dans l'historique du dépôt
  (préfixe optionnel par identifiant de tâche, ex. `P44: ...`) et ceux-ci
  sont écrit en anglais.
