<?php

namespace Database\Seeders\ruleset\Concerns;

use App\Models\ruleset\Ruleset;
use Database\Seeders\ruleset\RulesetSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait SeedsOnce
{
    /**
     * Ruleset ciblé par les seeders du catalogue, identifié par nom et version (et non par « le premier »).
     */
    protected function ruleset(): Ruleset
    {
        return Ruleset::query()
            ->where('name', RulesetSeeder::NAME)
            ->where('version', RulesetSeeder::VERSION)
            ->firstOrFail();
    }

    /**
     * Crée l'enregistrement via sa factory seulement s'il n'existe pas déjà (recherché par $keys),
     * pour que relancer `db:seed` sans `migrate:fresh` ne crée pas de doublons.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  list<string>  $keys  Colonnes qui identifient l'enregistrement.
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    protected function seedOnce(string $model, array $keys, array $attributes): Model
    {
        $identity = array_intersect_key($attributes, array_flip($keys));

        return $model::query()->where($identity)->first() ?? $model::factory()->create($attributes);
    }

    /**
     * Version en lot de `seedOnce` : 1 requête pour lire les lignes existantes (recherchées par $keys),
     * 1 `insert()` pour les manquantes, puis 1 relecture pour retourner tous les modèles.
     *
     * L'`insert()` contourne les factories, les casts et les événements du modèle : les lignes
     * doivent donc contenir toutes les colonnes requises, et seuls `created_at`/`updated_at` sont ajoutés.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  list<string>  $keys  Colonnes qui identifient chaque enregistrement.
     * @param  list<array<string, mixed>>  $rows
     * @return Collection<int, TModel>
     */
    protected function seedManyOnce(string $model, array $keys, array $rows): Collection
    {
        if ($rows === []) {
            return $model::query()->newCollection();
        }

        $identityOf = fn (array $row): array => array_intersect_key($row, array_flip($keys));
        $matching = fn (): Builder => $model::query()->where(function (Builder $query) use ($rows, $identityOf): void {
            foreach ($rows as $row) {
                $query->orWhere(function (Builder $identity) use ($row, $identityOf): void {
                    $identity->where($identityOf($row));
                });
            }
        });

        $existing = $matching()->get()
            ->map(fn (Model $existing): string => implode('|', array_map(fn (string $key): string => (string) $existing->getAttribute($key), $keys)))
            ->all();

        $now = now();
        $missing = array_values(array_filter(
            $rows,
            fn (array $row): bool => ! in_array(implode('|', array_map('strval', $identityOf($row))), $existing, true),
        ));

        if ($missing === []) {
            return $matching()->get();
        }

        $model::query()->insert(array_map(fn (array $row): array => $row + ['created_at' => $now, 'updated_at' => $now], $missing));

        return $matching()->get();
    }
}
