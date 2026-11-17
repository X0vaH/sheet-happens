# Contribuer à Sheet Happens

Ce guide décrit comment mettre en place un environnement de développement,
valider son travail avant de l'intégrer, et les normes de programmation
attendues sur ce projet.

## Mise en place

```bash
npm install
cp .env.example .env   # puis ajuster VITE_API_URL si le backend Laravel ne tourne pas sur localhost:8000
npm run dev
```

L'application est servie par Vite. Le backend (API Laravel) est un projet
séparé ; `VITE_API_URL` doit pointer vers son URL racine (sans `/api` à la
fin).

### Règles et skills IA

Les règles et skills IA partagés sont dans `agent/` (source de vérité) ;
chaque outil (`.cursor/`, `.claude/`) en reçoit une **copie** identique.
Procédure d’ajout : [AGENTS.md](AGENTS.md).

## Commandes à exécuter avant d'ouvrir une pull request

```bash
npm run test                   # lance la suite de tests (voir tests/)
npm run format                 # formatage du code
npm run lint                   # ESLint (voir eslint.config.js)
```

`npm run test`, `npm run format` et `npm run lint` doivent passer sans
erreur. Si des règles ou skills partagés sous `agent/` changent, leurs copies
dans `.cursor/` et `.claude/` doivent être mises à jour ; la revue PR
automatisée le vérifie (voir [AGENTS.md](AGENTS.md)).

Une partie de la suite de tests démarre automatiquement un serveur de
dev sur `127.0.0.1:4173` (voir `playwright.config.js`) et nécessite que
Microsoft Edge soit installé sur la machine (`channel: 'msedge'`) —
aucune autre étape manuelle n'est requise.

## Normes de programmation

Les tests d’interface utilisent `tests/helpers/auth.js` pour simuler la réponse
de session `/api/user`, avec `BYPASS_AUTH = false` dans `AuthProvider.jsx`.
Ils passent donc par les routes protégées et le fournisseur d’authentification
habituels, sans nécessiter un serveur Laravel ni de compte réel. La configuration
Playwright fixe l’URL API au serveur de test ; les appels utilisés sont interceptés.
Importer `test` et `expect` depuis cette fixture pour les tests de pages protégées.
Les tests de connexion utilisent `test.use({ authenticated: false })` et simulent
les réponses de connexion/déconnexion. Ils valident le frontend, pas les cookies
Sanctum ni la sécurité du backend réel.

- Noms de variables, fonctions, composants et classes clairs et
  descriptifs.
- Respecter les conventions de nommage React/JS (composants en PascalCase,
  hooks préfixés `use...`, etc.).
- Éviter la duplication de code : privilégier la réutilisation de
  composants (`src/components/`) et de fonctions utilitaires
  (`src/utils/`).
- Une fonction ou un composant devrait avoir une responsabilité claire et
  rester suffisamment court pour être facilement compréhensible.
- Valider les données provenant de l'utilisateur avant de les traiter ou
  de les envoyer à l'API.
- Utiliser des constantes plutôt que des valeurs magiques lorsque cela
  améliore la lisibilité (voir `src/configs/`).
- Corriger les avertissements et erreurs signalés par `npm run lint` avant
  de considérer une fonctionnalité comme terminée.
- Le style de code utilise des guillemets simples (`'`), des
  points-virgules en fin de ligne et une indentation de 4 espaces.
- Documenter les fonctions et composants avec des commentaires JSDoc
  lorsque leur comportement n'est pas évident à la seule lecture du code.

## Définition de « terminé »

Une fonctionnalité est considérée comme terminée lorsque :

- le développement est complété et fonctionne conformément au [cahier des
  charges](docs/cahier-des-charges.md) et aux [décisions du
  client](docs/rencontres-client.md) ;
- le code respecte les normes de programmation ci-dessus ;
- les validations nécessaires sont présentes (ex. empêcher les choix de
  personnage invalides) ;
- la fonctionnalité a été testée avec des cas normaux et des cas limites ;
- aucun problème critique connu ne subsiste ;
- l'intégration dans `develop` ne provoque pas de régression ;
- la documentation pertinente a été mise à jour ;
- l'interface fonctionne correctement sur ordinateur et sur mobile lorsque
  la fonctionnalité est concernée.

## Branches et pull requests

Voir [docs/git-conventions.md](docs/git-conventions.md) pour le modèle de
branches complet (git-flow), le workflow de tâche et les conditions de
fusion.

En résumé :

- développer dans une branche `feature/{tacheId}-nom-branche` ou
  `bugfix/...`, jamais directement dans `develop` ou `main` ;
- ouvrir la pull request vers `develop` une fois `npm run test`,
  `npm run format` et `npm run lint` validés localement (et copies
  `.cursor/` / `.claude/` à jour si `agent/` a changé) ;
- la fusion requiert la validation d'un agent **et** celle d'un humain.

## Documentation

Toute modification importante du fonctionnement de l'application doit
être reflétée dans la documentation correspondante (README, ce fichier,
ou les documents sous `docs/`).

## Langue

- La documentation et les issues sont rédigées en **français**, avec une
  terminologie uniforme.
- Le code (noms de variables, fonctions, composants, commentaires dans le
  code) et les commits sont écrit en **anglais**.
