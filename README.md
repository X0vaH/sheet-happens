# 🎲 Sheet Happens — Créateur de personnages D&D 5e

Application web full-stack qui guide l'utilisateur dans la **création et la progression de personnages de Donjons & Dragons (5e édition)** : choix de l'espèce et de la classe, répartition des caractéristiques, application automatique des règles, puis montée de niveau du niveau 1 au niveau 5.

L'expérience s'inspire de la création de personnage de *Baldur's Gate 3* : choix progressifs et effets visibles immédiatement.

> Projet d'équipe réalisé dans le cadre du cégep, pour un client réel. Ce dépôt regroupe les trois dépôts d'origine (API, frontend, infrastructure) en un seul monorepo prêt à lancer.

---

## ✨ Fonctionnalités

- **Comptes utilisateurs** : inscription, connexion, déconnexion, modification du nom d'utilisateur et du mot de passe (Laravel Sanctum, cookies SPA + CSRF).
- **Jusqu'à 5 personnages** par utilisateur (création, consultation, suppression).
- **Création guidée** :
  - espèces du *Player's Handbook* ;
  - 5 classes : Barbare, Clerc, Guerrier, Magicien, Roublard (une sous-classe par classe) ;
  - caractéristiques par **Array standard**, **4d6** ou saisie manuelle ;
  - historique, alignement, compétences, maîtrises, langues, équipement, dons et sorts ;
  - validation qui empêche les choix invalides.
- **Montée de niveau (1 → 5)** avec options calculées par l'API et application automatique des capacités.
- **Fiche de personnage complète**, calcul automatique des statistiques dérivées, responsive (ordinateur et mobile).
- Catalogue de règles (*ruleset*) mis en cache par Nginx.

## 🧱 Stack technique

| Couche | Technologies |
| --- | --- |
| Frontend | React 19, Vite, React Router, Axios, Playwright (tests E2E), ESLint, Prettier |
| Backend | PHP 8.4, Laravel 13, Laravel Sanctum, PHPUnit, Laravel Pint |
| Base de données | MySQL 8.4 |
| Infra | Docker Compose, Nginx (reverse proxy + cache), Mailpit, phpMyAdmin, Make |

## 🗂️ Structure du dépôt

```
sheet-happens/
├── backend/            API Laravel (ex-dépôt Sheet-Happens-Api)
├── frontend/           SPA React + Vite (ex-dépôt Sheet-Happens-Frontend)
├── docker/             Dockerfiles et config Nginx / API / frontend
├── scripts/            Création des .env et vérification de la stack
├── docs/               Documentation (infra, conventions Git)
├── docker-compose.yml  Orchestration de tous les services
└── Makefile            Commandes raccourcies
```

Architecture :

```
Navigateur ──► Nginx :80 ─┬─ /            ──► frontend (Vite dev server)
                          ├─ /api         ──► api (Laravel, php-fpm)
                          └─ /sanctum     ──► api
                                               │
                                               └──► MySQL
```

---

## 🚀 Installation et lancement (Docker — recommandé)

### Prérequis

- [Git](https://git-scm.com/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (ou Docker Engine + Docker Compose v2)
- *(optionnel)* `make`. Sur Windows, les commandes `docker compose` ci-dessous suffisent.
- Le **port 80** doit être libre (ainsi que 3306, 8025 et 8080).

### 1. Cloner le dépôt

```bash
git clone https://github.com/X0vaH/sheet-happens.git
cd sheet-happens
```

### 2. Créer les fichiers `.env`

Avec Make :

```bash
make ensure-env
```

Ou à la main :

```bash
cp .env.development .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

(Sur Windows sans Make : `powershell -ExecutionPolicy Bypass -File scripts/ensure-env.ps1`.)

### 3. Démarrer la stack

```bash
docker compose up -d --build
```

Au premier démarrage, les conteneurs installent automatiquement les dépendances Composer et npm, génèrent la clé Laravel (`APP_KEY`), exécutent les migrations et chargent le catalogue de règles (seeders). Compter quelques minutes ; suivre l'avancement avec :

```bash
docker compose logs -f api frontend
```

### 4. Ouvrir l'application

| Service | URL |
| --- | --- |
| Application | <http://127.0.0.1> |
| Mailpit (courriels de dev) | <http://127.0.0.1:8025> |
| phpMyAdmin | <http://127.0.0.1:8080> |

> 💡 Sur Windows, utiliser `127.0.0.1` plutôt que `localhost` (la résolution IPv6 de `localhost` peut ralentir les requêtes et brise les cookies Sanctum).

Créer un compte à partir de la page d'inscription, puis créer un premier personnage.

### 5. Arrêter

```bash
docker compose down        # arrête les conteneurs
docker compose down -v     # arrête et supprime aussi la base de données
```

### Commandes utiles

| Commande | Description |
| --- | --- |
| `make up-dev` | Crée les `.env` manquants puis démarre la stack |
| `make down` | Arrête la stack |
| `make migrate-fresh-seed` | Réinitialise la base de données et relance les seeders |
| `make php artisan route:list` | Exécute une commande `php` dans le conteneur API |
| `make smoke` | Vérifie que la stack fonctionne (santé, seed, Sanctum, cache) |

---

## 🛠️ Lancement sans Docker (optionnel)

<details>
<summary>Backend et frontend séparés</summary>

**Prérequis :** PHP 8.4, Composer, Node.js 20+, MySQL 8.

Backend :

```bash
cd backend
composer install
cp .env.example .env          # ajuster DB_HOST=127.0.0.1 et les identifiants MySQL
php artisan key:generate
php artisan migrate --seed
php artisan serve             # http://127.0.0.1:8000
```

Frontend (dans un autre terminal) :

```bash
cd frontend
npm install
cp .env.example .env          # VITE_API_URL=http://127.0.0.1:8000
npm run dev
```

</details>

## 🧪 Tests et qualité

```bash
# Tests backend (PHPUnit)
docker compose exec api php artisan test

# Style PHP
docker compose exec api ./vendor/bin/pint --test

# Frontend : lint, formatage, tests E2E Playwright
cd frontend
npm run lint
npm run format:check
npx playwright install
npm test
```

## 📚 Documentation complémentaire

- [docs/infra.md](docs/infra.md) — détails de l'infrastructure Docker, Nginx et Sanctum
- [backend/README.md](backend/README.md) — API Laravel
- [frontend/README.md](frontend/README.md) — application React
- [frontend/docs/cahier-des-charges.md](frontend/docs/cahier-des-charges.md) — cahier des charges du client
- [CONTRIBUTING.md](CONTRIBUTING.md) — normes et processus de contribution

## 👥 Équipe

Projet réalisé en équipe :

- **Alex Héroux** ([@X0vaH](https://github.com/X0vaH))
- [@Kouameanaset](https://github.com/Kouameanaset)
- Thomas Lagacé ([@ThomasLagace](https://github.com/ThomasLagace))
- Rémi Labelle ([@RemiLabelle](https://github.com/RemiLabelle))
- Jacob Bergeron ([@BigJake22](https://github.com/BigJake22))

Dépôts d'origine : [Sheet-Happens-Frontend](https://github.com/Kouameanaset/Sheet-Happens-Frontend) · [Sheet-Happens-Api](https://github.com/Kouameanaset/Sheet-Happens-Api) · [Sheet-Happens-Infra](https://github.com/ThomasLagace/Sheet-Happens-Infra)
