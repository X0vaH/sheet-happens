# Montée de niveau — règles 2014

Les routes `GET /api/characters/{character}/level-up/options` et
`POST /api/characters/{character}/level-up` nécessitent une session Sanctum
et vérifient que le personnage appartient à l’utilisateur.

## Installation sur une base existante

```bash
php artisan migrate
php artisan db:seed --class='Database\Seeders\ruleset\RulesetDatabaseSeeder'
```

La migration ajoute `features.choice_count`. Les seeders actualisent les
descriptions et relations du catalogue sans supprimer les personnages. Le
déblocage existant de l’École d’évocation est déplacé au niveau 2 en conservant
l’identifiant de sous-classe. Les personnages déjà avancés ne sont pas rétrogradés.

## Choix et enregistrement

- Prochain niveau uniquement, jusqu’à 5. Une transaction verrouille le personnage
  et revalide le niveau pour empêcher les soumissions doubles.
- PV fixes, minimum 1 ; hausse identique des PV actuels et maximum. Une hausse
  du modificateur de Constitution s’applique aussi aux niveaux précédents.
- Sous-classe automatique : aucun choix de sous-classe requis dans le POST.
- Le niveau 4 exige un `feat_id` de la liste proposée : Ability Score
  Improvement (ASI) ou un des dix dons 2014 listés ci-dessous.
  ASI attribue deux points, sans dépasser 20 ; un autre don remplace cette
  hausse et exige `ability_increases: []`, conformément aux règles 2014.
  Chaque don expose son `ability_increase_count` (2 ou 0) dans les options.
  `progression.ability_points` reste le budget ASI du niveau, pas un cumul
  avec les autres dons ; le client utilise le budget du don sélectionné.
  Un don déjà acquis est exclu, sauf ASI qui peut être repris.
- Chaque élément de `feature_choices` contient `feature_id` et `option_ids`.
  Le nombre, l’unicité et l’appartenance des options sont contrôlés.
- Le Maître de guerre choisit trois manœuvres parmi les seize de 2014,
  et un outil d’artisan pour Élève de guerre. Les choix sont conservés
  dans `character_feature_choices`, et la maîtrise de l’outil est enregistrée.
- Le Magicien apprend deux sorts accessibles et inconnus par niveau. Les deux
  lanceurs gagnent un tour de magie au niveau 4. Le Clerc reçoit l’accès aux
  sorts de sa liste, ainsi que les sorts de domaine aux niveaux 3 et 5 ; ceci
  ne remplace pas la gestion de préparation quotidienne, hors de ce parcours.
- Les emplacements sont actualisés sans remettre à zéro ceux déjà dépensés.

Le catalogue de progression 2–5 suit 2014. L’identifiant et le libellé historique
du ruleset de création (`2024`) sont conservés pour ne pas recréer le catalogue
ni déplacer les personnages : cette tâche ne convertit pas toutes les règles
de création du projet vers 2014.

## Sources

### Dons proposés au niveau 4

La sélection comprend Combattant à deux armes, Combattant monté,
Expert de la charge, Explorateur de donjons, Maître-arbalétrier,
Maître des armes à deux mains, Mobile, Sentinelle, Tireur d’élite et Tueur
de mages : dix dons du Player’s Handbook **2014**, sans prérequis ni choix
supplémentaire à enregistrer et sans hausse de caractéristique.
Leurs descriptions françaises sont des résumés d’une phrase rédigés pour
l’application ; les effets en combat restent à appliquer par les joueurs.
Cette sélection ne représente pas la totalité des dons du livre.

Références : [règle officielle du remplacement d’ASI](https://www.dndbeyond.com/sources/dnd/basic-rules-2014/customization-options)
et [index français des dons 2014, noms et prérequis](https://www.aidedd.org/dnd-filters/dons.php).
La catégorie `general` est une convention de stockage du projet : les
catégories de dons de 2024 ne sont pas appliquées aux règles 2014.

### Descriptions des sorts : SRD 5.1

Les descriptions des sorts sont des résumés en français adaptés du SRD 5.1
(règles 2014), dont la traduction de référence est distribuée par 5e-bits.
Voir [les licences et la révision importée](../database/seeders/ruleset/data/SRD-LICENSE.md).
Les JSON sont versionnés ; les seeders fonctionnent hors ligne. Le script
`database/seeders/ruleset/data/import-srd-spells.mjs` permet un nouvel import
explicite des textes complets avec Node ; cet import remplace les résumés et
doit être révisé avant intégration. Les descriptions des manœuvres
sont des résumés rédigés pour l’application.

## Ajustements de la review

- Les PV rétroactifs comparent les gains avant/après Constitution, avec un
  minimum de 1 par niveau et le dé maximal au premier niveau.
- La création enregistre le niveau 1 et sa relation à la classe.
- Les routes de progression renvoient 404 pour le personnage d’un autre utilisateur.
- `feature_options.tool_id` remplace la recherche d’outils par nom lors de la montée.
  Appliquer `php artisan migrate` avant les seeders ; la migration relie aussi
  les options existantes dont les noms correspondent encore.
- Relancer `php artisan db:seed` actualise les descriptions de classes.
  Le renommage de Frénésie conserve son identifiant. Le déplacement du
  déblocage d’une sous-classe conserve les autres capacités à leur niveau.
- Les sorts, outils et emplacements sont écrits par lots dans la transaction.

## Vérification

`tests/Feature/LevelUpControllerTest.php` vérifie les cinq classes de 1 à 5,
la persistance, les PV exacts et rétroactifs, le plafond de 20, les choix
invalides, les sorts connus/inaccessibles, la propriété, le niveau maximum,
les doublons de requêtes et la relance des seeders avec des personnages présents.

Le test vérifie la présence des 139 sorts attendus, un résumé non vide pour
chacun et sa conservation en base. Il vérifie aussi les descriptions exposées
par les options de montée de niveau du Magicien et du Clerc.
Voir [la référence de test](../tests/Fixtures/README.md).

```bash
composer test
./vendor/bin/pint --test
```

Le test de concurrence utilise deux processus HTTP Laravel et une vraie
base MySQL temporaire : un seul POST réussit, l’autre retourne 422, et les
PV ainsi que le niveau ne sont appliqués qu’une fois.
SQLite en mémoire ne permet pas de vérifier ce verrouillage MySQL.
Depuis le dossier backend, avec Docker et l’image Sail du projet construite :

```bash
docker compose -p sheet-happens-concurrency -f compose.testing.yaml run --rm tests
docker compose -p sheet-happens-concurrency -f compose.testing.yaml down -v
```

Ce projet Docker n’expose aucun port MySQL sur l’hôte et ne monte aucune
base de développement ; `down -v` supprime uniquement ses volumes de test.
Sans Docker : `composer test:concurrency`, avec `TEST_MYSQL_HOST`,
`TEST_MYSQL_PORT`, `TEST_MYSQL_USER` et `TEST_MYSQL_PASSWORD` pour un serveur
de test autorisant la création de bases temporaires.

### Formulation pour la PR

Montée de niveau 2–5 avec PV fixes, sous-classe automatique et, au niveau 4,
choix entre ASI et dix dons du PHB 2014 ; les descriptions des sorts sont
des résumés adaptés du SRD 5.1 français, avec attribution versionnée.
