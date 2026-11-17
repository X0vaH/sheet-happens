# AGENTS.md

Sheet Happens est une application web React (Vite) permettant de créer,
sauvegarder et gérer des personnages de Donjons & Dragons 5e édition. Le
backend (API Laravel) est un projet séparé, consommé via Axios.

## Commandes

```bash
npm install                 # installation des dépendances
npm run dev                 # serveur de dev Vite
npm run build               # build de production
npm run test                # suite de tests (tests/), peut démarrer le serveur de dev automatiquement
npm run format              # formatage du code
npm run lint                # ESLint (eslint.config.js)
```

Exécuter `npm run test`, `npm run format` et `npm run lint` avant de
considérer une tâche terminée.

## Langue

La documentation et les issues sont rédigées en **français**. Le code
(variables, fonctions, composants, commentaires dans le code) et les
commits sont écrit en **anglais**.

## Où trouver les choses

- `src/pages/` — pages routées (Home, LoginPage, SignupPage,
  CharacterSheet, AccountSettingsPage, création de personnage sous
  `pages/characterCreation/`).
- `src/components/` — composants réutilisables, organisés par domaine
  (`characterCreation/`, `characterSheet/`, `home/`, `global/`).
- `src/context/` et `src/hooks/` — authentification (`AuthContext`,
  `AuthProvider`, `useAuth`), limite de 5 personnages (`CharacterLimitProvider`,
  `useCharacterLimit`, constante dans `configs/characterLimit.js`) et catalogue de création
  (`CreationOptionsProvider`, `useCreationOptions`). `CharacterLimitProvider` partage
  aussi la liste des personnages (`characters`, tenue à jour par l'accueil) : l'étape
  « Informations » s'en sert pour le choix de l'emplacement (`order_index`), sans
  nouvelle requête. Un emplacement vide cliqué à l'accueil est passé en `state`
  de navigation (`orderIndex`) et préselectionné ; un emplacement occupé (calculé
  avec `buildSlots`, comme le carrousel) n'est pas sélectionnable.
- `src/configs/` — données de référence locales pour la création de
  personnage, utilisées pour ce que le catalogue de l'API ne fournit pas
  encore (règles de CA par classe, bonus de maîtrise, CA sans armure) :
  `characterCreation.js`, `characterStorage.js` (clés du brouillon). Les
  stats d'armure (`item.armor`) et l'ordre des caractéristiques
  (`options.abilities`, trié par id) viennent du catalogue. Les pages et
  composants n'y accèdent que par les accesseurs de
  `src/utils/characterCreation/catalogExtras.js`, qui prennent le catalogue
  (`options`) : quand l'API fournira une donnée, seul l'accesseur change.
  Alignements, maîtrises de classe et descriptions de langues viennent
  désormais du catalogue (`options.alignments`, `options.classes[].proficiencies`,
  `options.languages[].description`).
  `characters.js` et `fightingStyles.js` ne servent plus qu'au catalogue de
  personnages prédéfinis (fiche, accueil) ; le style de combat de la création
  vient du catalogue, comme n'importe quelle aptitude de classe à options.
- `src/utils/characterCreation/creationSteps.js` — étapes actives et
  validation du parcours ; `src/hooks/` contient aussi les hooks de la
  création (`useDraftState`, `useCharacterSummary`, `useSaveCharacter`, etc.).
- `src/api/axios.js` — client HTTP configuré avec `VITE_API_URL` (voir
  `.env.example`) ; `src/api/characterCreation.js` — catalogue de
  création et `POST /api/characters`.
- `src/utils/characterCreation/characterPayload.js` — conversion du
  brouillon (noms du catalogue) en corps de requête (identifiants).
- `tests/` — suite de tests du projet, un fichier par domaine
  fonctionnel (`class.spec.js`, `spells.spec.js`, `creation.spec.js`,
  etc.) ; `tests/helpers/` contient les utilitaires partagés.
  `tests/helpers/test.js` simule l'API (session, catalogue de
  `tests/fixtures/creationOptions.js`, création) : les specs importent
  `test` depuis ce fichier et n'ont pas besoin du backend.

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

- Vue d'ensemble du projet, installation, structure →
  [README.md](README.md)
- Normes de programmation, commandes de test/lint/format, définition de
  « terminé », processus de PR → [CONTRIBUTING.md](CONTRIBUTING.md)
- Modèle de branches Git et workflow de tâche →
  [docs/git-conventions.md](docs/git-conventions.md)
- Inventaire des fichiers sous `agent/` → [agent/README.md](agent/README.md)
- Portée, exigences et risques du projet →
  [docs/cahier-des-charges.md](docs/cahier-des-charges.md)
- Décisions et retours du client au fil des rencontres (**source de
  vérité en cas de conflit avec le cahier des charges**) →
  [docs/rencontres-client.md](docs/rencontres-client.md)
