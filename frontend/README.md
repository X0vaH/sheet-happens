# Sheet Happens

Sheet Happens est une application web (React + Vite) permettant de créer,
sauvegarder et gérer des personnages de Donjons & Dragons 5e édition — choix
de race, de classe, répartition des caractéristiques, application
automatique des règles de base, et progression du personnage du niveau 1 au
niveau 5.

Ce dépôt contient le **frontend**. L'application consomme une API Laravel
(dépôt séparé) via Axios.

## Prérequis

- [Node.js](https://nodejs.org/) et npm.
- Une instance de l'API backend Sheet Happens (Laravel), accessible
  localement ou à distance.

## Installation

```bash
npm install
cp .env.example .env
```

Ajuster `VITE_API_URL` dans `.env` pour pointer vers l’URL racine de l’API
Laravel (sans `/api` à la fin) si elle ne tourne pas sur
`http://localhost:8000`.

## Démarrage

```bash
npm run dev
```

## Commandes disponibles

| Commande          | Description                               |
| ----------------- | ----------------------------------------- |
| `npm run dev`     | Démarre le serveur de développement Vite. |
| `npm run build`   | Build de production.                      |
| `npm run preview` | Sert le build de production localement.   |
| `npm run test`    | Lance la suite de tests du projet.        |
| `npm run format`  | Formate le code.                          |
| `npm run lint`    | Analyse le code avec ESLint.              |

## Structure du projet

```
agent/            Règles et skills partagés pour agents IA (source de vérité)
src/
  api/            Client Axios configuré avec VITE_API_URL
  components/     Composants réutilisables, organisés par domaine
  configs/        Données de référence (races, classes, sorts, stockage local)
  context/        Contextes d'authentification et de limite de personnages
  hooks/          Hooks personnalisés (ex. useAuth)
  pages/          Pages routées (accueil, connexion, fiche de personnage, création...)
  styles/         Feuilles de style par page/composant
tests/            Tests du projet, un fichier par domaine fonctionnel
```

Les configs d’agents (`.cursor/`, `.claude/`) contiennent des copies de
`agent/`. Procédure d’ajout : [AGENTS.md](AGENTS.md).

## Documentation du projet

Un utilisateur ne peut pas avoir plus de 5 personnages. À la limite, les liens
« Créer un personnage » sont désactivés avec le message « Limite de 5 personnages
atteinte », et `/character/create` affiche ce message au lieu du formulaire. Si le
comptage échoue ou est périmé, la sauvegarde reste protégée par la réponse `403`
de l’API, dont le message est affiché sans perdre le brouillon.

La montée de niveau est accessible par `/character/:characterId/level-up`,
jusqu’au niveau 5, avec validation et sauvegarde par l’API. Le bouton « Améliorer »
sera branché ultérieurement. Voir [le parcours de montée de niveau](docs/montee-de-niveau.md)
pour les règles, le contrat API et la mise à jour du catalogue.
La saisie des caractéristiques propose les modes Manuel, Dés et Standard.
En mode Manuel, les six valeurs sont des entiers de 3 à 20 et la réserve de
valeurs disponibles est masquée. Le bouton « Continuer » affiche les champs
à corriger dans le popup rouge si une valeur est vide ou invalide.

La création de personnage (niveau 1) s’appuie sur l’API :
`GET /api/rulesets/current/character-creation-options` fournit le catalogue
(races, classes, sous-classes, historiques, compétences, langues, objets),
chargé une fois pour toutes les étapes, et le résumé envoie le personnage en
une seule requête `POST /api/characters`. Le brouillon conserve les noms du
catalogue ; ils sont convertis en identifiants à l’envoi. Les points de vie
sont calculés par le serveur. Ce que le catalogue ne fournit pas encore
(équipement de départ, sorts au choix, langues des races, nombre de
compétences de classe) vient des fichiers de `src/configs/` ; les choix qui
n’existent pas dans le catalogue sont listés dans le résumé comme
« non enregistrés ».

L’équipement possède sa propre étape après la classe et avant les
caractéristiques. Les choix d’équipement sont conservés par classe ; le
Guerrier peut préciser ses armes, enregistrées dans `fighterWeapons`.
Les sous-classes et les aptitudes de classe à options (comme le style de
combat du Guerrier) restent dans l’étape Classe : `ChooseClass.jsx` traite
toute aptitude de niveau 1 dont le catalogue expose des `options` comme un
choix générique, envoyé dans `feature_choices` à la sauvegarde ; aucun code
spécifique par classe n’est nécessaire.

L’historique se choisit après les caractéristiques et avant les compétences.
Ses deux compétences sont accordées automatiquement, en plus du nombre de
choix de classe, et ne peuvent pas être choisies une seconde fois. Un changement
d’historique retire les choix de classe devenus redondants ; le joueur complète
ensuite ses choix. Le résumé et la sauvegarde réunissent les deux sources.

La section Langues propose autant de sélecteurs que la race et l’historique
accordent de langues au choix. Les langues déjà connues et les doublons sont
exclus. Tous les choix sont requis avant de continuer. La sauvegarde envoie
chaque langue avec sa source (`species`, `background` ou `choice`) et toutes
les maîtrises de compétences (historique et classe).

Le Guerrier peut choisir un style de combat dans l’étape Classe pendant la
création, comme option de l’aptitude « Style de combat » fournie par le
catalogue. Le choix est conservé dans le brouillon (`featureOptions`, par id
d’aptitude) et affiché dans les résumés ; il est envoyé dans
`feature_choices` à la sauvegarde. Le style Défense ajoute automatiquement
+1 à la CA calculée pendant la création si une armure est portée ; un
bouclier seul ne suffit pas. Les autres effets sont présentés comme
rappels, sans automatisation des jets, des dégâts ou des réactions. La
fiche et les cartes d’accueil affichent le champ `fightingStyle` lorsqu’il
est fourni par les données du personnage ; elles utilisent encore le
catalogue de personnages prédéfinis, distinct du parcours de création.

- [CONTRIBUTING.md](CONTRIBUTING.md) — mise en place, normes de
  programmation, définition de « terminé », processus de pull request.
- [AGENTS.md](AGENTS.md) — repères pour les agents IA (commandes, procédure
  règles/skills sous `agent/` et copies vers les outils).
- [docs/git-conventions.md](docs/git-conventions.md) — modèle de branches
  Git et workflow de tâche.
- [docs/cahier-des-charges.md](docs/cahier-des-charges.md) — portée,
  exigences et risques du projet.
- [docs/rencontres-client.md](docs/rencontres-client.md) — décisions et
  retours du client au fil des rencontres (source de vérité en cas de
  conflit avec le cahier des charges).
