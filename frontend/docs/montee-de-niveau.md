# Montée de niveau

Le parcours est accessible directement par `/character/:characterId/level-up`.
Le bouton « Améliorer » reste désactivé. Le branchement de la fiche sur l’API
est traité séparément : la fiche de démonstration ne permet pas encore de
vérifier une sauvegarde et redirige vers l’accueil pour un identifiant inconnu.
`useCharacter` et le chargement des options partagent `useApiData`, avec
rejet des réponses obsolètes dans le hook. `useLevelUpForm` regroupe les choix, les
calculs, la validation et l’envoi ; la page conserve le rendu.

## Comportement

- Une page regroupe tous les choix fournis par `level-up/options`.
- Le nom du personnage apparaît entre le titre et le prochain niveau.
- Une boîte centrée indique le chargement du personnage puis des options.
- PV fixes : moitié du dé de vie + 1 + modificateur de Constitution, minimum 1.
  Les PV avant et après apparaissent dans la boîte « Niveau Supérieur » et se
  recalculent avec le supplément rétroactif si la Constitution augmente.
  Le minimum de 1 PV est appliqué séparément à chaque ancien niveau,
  en utilisant le dé maximal au niveau 1 et le gain fixe ensuite.
- Une seule sous-classe par classe, attribuée automatiquement. En 2014, le
  Magicien la reçoit au niveau 2 ; Barbare, Guerrier et Roublard au niveau 3.
  Le Clerc conserve son domaine acquis à la création.
- Niveau 4 : les choix de caractéristiques restent affichés, sans bouton préalable.
  Ability Score Improvement est présélectionné : +2 à une caractéristique
  ou +1 à deux, avec un plafond de 20. Les autres dons proposés par l’API
  remplacent cette amélioration. Le budget vient du don choisi et les
  augmentations sont associées aux identifiants des caractéristiques.
- Les options de capacités ont un nombre de sélections imposé par le backend.
- Les capacités automatiques sont regroupées dans une boîte. Chaque capacité
  nécessitant des choix dispose de sa propre boîte, avec deux options par ligne.
  Les descriptions des options utilisent la même infobulle que les sorts.
- Le Magicien choisit deux sorts par niveau. Clerc et Magicien gagnent un tour
  de magie au niveau 4. Les sorts accessibles automatiquement au Clerc sont
  présentés séparément des sorts à choisir ; ils ne sont pas tous préparés.
- Les descriptions des capacités, dons, sous-classes et sorts sont affichées.
- Les sorts et tours de magie utilisent le composant partagé `DescriptionInfo`,
  également présent dans la création de personnage : survol à la souris,
  focus au clavier ou bouton « i » sur téléphone. Un clic ailleurs ou Échap
  ferme la description sans modifier la sélection du sort.
- Le bouton de confirmation est désactivé tant qu’un choix manque.
- Une confirmation précède l’envoi. Annuler ferme cette confirmation ; Retour
  quitte le formulaire sans enregistrer.
- En cas d’erreur, le message reste visible et les choix sont conservés.
- Après réussite, retour à `/character/:characterId`.
- Au niveau 5, un écran « Niveau maximum atteint » offre un retour à la fiche,
  sans appeler l’API des options.

## Contrat API

`GET /api/characters/{id}/level-up/options` fournit `next_level`, `hit_die`,
`hit_points`, `progression`, `granted_spells` et `available_options`.
Chaque capacité contient `choice_count` et `options`. Le don apparaît dans
`available_options.feats` uniquement au niveau 4.

`POST /api/characters/{id}/level-up` reçoit :

- `target_level` : prochain niveau ;
- `hit_point_method` : `fixed` ; aucun lancer de dés ;
- `feat_id` : don choisi au niveau 4, sinon `null` ;
- `feature_choices` : `{ feature_id, option_ids }` pour chaque capacité à choix ;
- `ability_increases` : `{ ability_id, increase_amount }` ;
- `spell_ids` : nouveaux sorts et tours de magie choisis.

Le backend détermine la sous-classe automatiquement. Le verrou transactionnel
et la validation de `target_level` empêchent d’appliquer deux fois une montée.
Les PV actuels augmentent du même montant que les PV maximum.

## Mise à jour du backend

Dans le dossier backend, avec ses services Docker actifs :

```bash
docker compose exec -T laravel.test php artisan migrate
docker compose exec -T laravel.test php artisan db:seed --class='Database\Seeders\ruleset\RulesetDatabaseSeeder'
```

Ces commandes ajoutent la colonne de nombre de choix et actualisent le catalogue
sans supprimer les personnages. Le catalogue de progression suit les règles
2014, avec des résumés français adaptés du SRD 5.1 et leurs attributions conservées dans
`backend/database/seeders/ruleset/data/SRD-LICENSE.md`. Il ne constitue pas une
reproduction complète du Player’s Handbook.

## Vérifications

`tests/levelUp.spec.js` couvre les vingt transitions classe/niveau, les choix
manquants, les capacités à options, le don, les erreurs et réessais, l’annulation,
le niveau maximum, le chargement et les tailles de 320 à 1920 pixels.
Les tests utilisent `tests/helpers/levelUpApi.js` pour simuler les deux routes.
Les tests PHPUnit utilisent les vrais seeders et une base SQLite en mémoire.
