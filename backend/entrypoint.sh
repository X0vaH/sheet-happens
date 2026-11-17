#!/usr/bin/env bash
set -euo pipefail

# Normalize a dotenv value. Empty, whitespace, "" and '' are all empty.
# Callers must not print the result when the value is a secret.
normalize_env_value() {
    local value="${1-}"
    local length first last

    value="${value%$'\r'}"
    value="${value#"${value%%[![:space:]]*}"}"
    value="${value%"${value##*[![:space:]]}"}"

    length="${#value}"
    if [ "$length" -ge 2 ]; then
        first="${value:0:1}"
        last="${value:length-1:1}"
        if { [ "$first" = '"' ] && [ "$last" = '"' ]; } || { [ "$first" = "'" ] && [ "$last" = "'" ]; }; then
            value="${value:1:length-2}"
        fi
    fi

    printf '%s' "$value"
}

read_dotenv() {
    local key="$1"
    local line=""

    if [ -f .env ]; then
        line="$(grep -m 1 -E "^${key}=" .env || true)"
    fi

    if [ -z "$line" ]; then
        printf '%s' ""
        return
    fi

    normalize_env_value "${line#*=}"
}

ensure_app_key() {
    local current file_key app_env

    current="$(normalize_env_value "${APP_KEY-}")"
    if [ -n "$current" ]; then
        export APP_KEY="$current"
        echo "APP_KEY configured from the environment."
        return
    fi

    file_key="$(read_dotenv APP_KEY)"
    if [ -n "$file_key" ]; then
        export APP_KEY="$file_key"
        echo "APP_KEY configured from .env."
        return
    fi

    app_env="$(normalize_env_value "${APP_ENV-production}")"
    case "$app_env" in
        local|development|testing)
            if [ ! -f .env ]; then
                if [ ! -f .env.example ]; then
                    echo "APP_KEY is missing and there is no .env file to write one into." >&2
                    exit 1
                fi
                cp .env.example .env
            fi
            echo "APP_KEY is missing. Generating one for the ${app_env} environment."
            php artisan key:generate --force --no-interaction
            file_key="$(read_dotenv APP_KEY)"
            if [ -z "$file_key" ]; then
                echo "APP_KEY generation did not write a usable key." >&2
                exit 1
            fi
            export APP_KEY="$file_key"
            echo "APP_KEY configured."
            ;;
        *)
            echo "APP_KEY is missing. Provide it as an Infra environment variable or secret. A new key will not be generated." >&2
            exit 1
            ;;
    esac
}

load_database_env() {
    local key current

    for key in DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
        current="$(normalize_env_value "${!key-}")"
        if [ -z "$current" ]; then
            current="$(read_dotenv "$key")"
        fi
        if [ -n "$current" ]; then
            printf -v "$key" '%s' "$current"
            export "$key"
        fi
    done

    if [ -z "${DB_CONNECTION:-}" ]; then
        export DB_CONNECTION="mysql"
    fi

    : "${DB_HOST:?DB_HOST is required}"
    : "${DB_PORT:?DB_PORT is required}"
    : "${DB_DATABASE:?DB_DATABASE is required}"
    : "${DB_USERNAME:?DB_USERNAME is required}"
    : "${DB_PASSWORD:?DB_PASSWORD is required}"
}

wait_for_database() {
    local attempt=0
    local max_attempts=30

    echo "Waiting for database to be ready..."

    until php -r 'try { $pdo = new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE") . ";charset=utf8mb4", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [PDO::ATTR_TIMEOUT => 2]); $pdo->query("SELECT 1"); exit(0); } catch (Throwable $e) { exit(1); }' >/dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "Database did not become ready after ${max_attempts} attempts." >&2
            exit 1
        fi
        sleep 2
    done

    echo "Database is ready."
}

cd /var/www/html

ensure_app_key
load_database_env
wait_for_database
php artisan migrate --force --no-interaction
# Catalog seeders use SeedsOnce so re-running on every boot does not duplicate rows.
php artisan db:seed --force --no-interaction

# Drop Nginx's shared fastcgi cache for the ruleset catalog (volume nginx-cache).
if [ -d /var/cache/nginx ]; then
    echo "Purging Nginx ruleset catalog cache..."
    rm -rf /var/cache/nginx/*
fi

if [ -d storage ] && [ -d bootstrap/cache ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi

exec php-fpm --nodaemonize
