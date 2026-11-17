# Cahier des charges — Sheet Happens

Résumé de la portée, des exigences et des risques établis dans le cahier
des charges et la charte de projet du client (Éthan) au démarrage du
projet.

> **Ce document peut devenir désuet.** Le client fait évoluer ses attentes
> au fil des rencontres. **En cas de conflit entre ce document et
> [rencontres-client.md](rencontres-client.md), c'est
> `rencontres-client.md` qui fait foi** — il reflète l'état le plus récent
> de la portée réelle du projet, pas la portée figée au démarrage.

## Contexte et objectif

La création d'un personnage de Donjons & Dragons peut rapidement devenir
complexe, particulièrement pour un nouveau joueur : choix de race et de
classe, répartition des caractéristiques, application des
bonus/maîtrises/capacités, calculs de progression à chaque montée de
niveau.

Sheet Happens vise à guider l'utilisateur à travers ces étapes et à
appliquer automatiquement les règles prises en charge, en s'inspirant de
l'expérience de création de personnage de *Baldur's Gate 3* (choix
progressifs, effets visibles immédiatement).

Ce dépôt contient l'**API backend** (Laravel) du projet. Elle expose
l'authentification, la persistance des utilisateurs et des personnages,
et applique les règles de validation qui ne peuvent pas être uniquement
côté client. Le frontend (React) est un dépôt séparé qui consomme cette
API.

## Portée

### Inclus

- Une API web servant un frontend React responsive (ordinateur et
  mobile).
- Création de compte et authentification (par session, via Laravel
  Sanctum).
- Jusqu'à **5 personnages sauvegardés** par utilisateur, avec opérations
  CRUD complètes.
- Persistance des choix de création guidée d'un personnage : races de
  base du *Player's Handbook* (5e édition), et 5 classes : **Barbare,
  Clerc, Guerrier, Magicien, Roublard**.
- Progression des personnages du **niveau 1 au niveau 5**, avec
  application automatique des capacités/statistiques à chaque montée.
- Support d'**une seule sous-classe par classe**.
- Génération des caractéristiques par **Array standard** ou **4d6**, plus
  modification manuelle libre.
- Calcul automatique des statistiques dérivées.
- Gestion des compétences, maîtrises et capacités raciales/de classe,
  avec validation empêchant les choix invalides.
- Sauvegarde des données dans une base de données relationnelle.

### Exclus

- Le multiclassage.
- Toute classe autre que les 5 listées ci-dessus.
- Les races ou contenus provenant d'autres livres/extensions.
- Les règles personnalisées.
- La progression au-delà du niveau 5.
- La gestion de campagnes et les combats en temps réel.
- Une application mobile native (le web responsive suffit, côté
  frontend).

### Fonctionnalité souhaitée (non essentielle)

- Exportation de la fiche de personnage en format PDF. Les
  fonctionnalités essentielles doivent toujours être priorisées avant
  celle-ci.

## Exigences non fonctionnelles clés (côté API)

- Mots de passe chiffrés (hashés) ; un utilisateur ne peut ni consulter
  ni modifier les personnages d'un autre utilisateur.
- L'API doit rejeter ou signaler les choix qui ne respectent pas les
  règles prises en charge (ex. compétence déjà acquise via la race,
  équipement sans la maîtrise correspondante).
- Temps de réponse raisonnable sur les actions principales (en moyenne <
  2 secondes).
- Code organisé pour faciliter la maintenance et l'ajout futur de
  races/classes/fonctionnalités, en respectant les principes SOLID et le
  standard PSR-12 (voir [CONTRIBUTING.md](../CONTRIBUTING.md)).

## Risques principaux à surveiller

- **Complexité des règles D&D** : races, classes, compétences, capacités
  et niveaux représentent un volume de règles important à implémenter
  correctement côté serveur.
- **Dérive de portée** : l'ajout de races, classes, sous-classes ou
  fonctionnalités non prévues peut faire gonfler la charge de travail —
  protéger les fonctionnalités obligatoires contre les ajouts souhaités.
- **Erreurs de calcul** : une erreur dans l'implémentation des règles
  peut rendre une fiche de personnage invalide.
- **Sorts (Clerc, Magicien)** et **sous-classes** : sources potentielles
  de nombreux cas particuliers dans le modèle de données.
- **Temps de développement limité** : les fonctionnalités secondaires
  (ex. export PDF) doivent céder la priorité aux fonctionnalités
  obligatoires si le temps manque.

## Parties prenantes

- **Utilisateurs finaux** : membres des clubs du client, majoritairement
  des joueurs novices de D&D (voir
  [rencontres-client.md](rencontres-client.md)).

## Documents liés

- [Rencontres client](rencontres-client.md) — décisions et retours du
  client au fil du projet (source de vérité en cas de conflit).
- [Conventions Git](git-conventions.md) — modèle de branches et processus
  de pull request.
- [CONTRIBUTING.md](../CONTRIBUTING.md) — normes de programmation,
  commandes de test/lint/format.
