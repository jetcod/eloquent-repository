<?php

namespace Jetcod\LaravelRepository\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface EloquentRepositoryInterface.
 */
interface EloquentRepositoryInterface
{
    public function insert(array $rows): bool;

    public function create(array $attributes): Model;

    public function delete(Model $model): bool;

    public function destroy($ids): int;

    public function update(Model $model, array $data, array $fillable = []): Model;

    public function updateOrCreate(array $data, array $conditions);

    public function find(int $id): ?Model;

    public function findOneBy(array $conditions, array $relations = []): ?Model;

    public function findBy(array $conditions, array $relations = [], bool $paginate = true, int $pageSize = 10);

    public function findAll(): Collection;

    public function countBy(array $conditions): int;
}
