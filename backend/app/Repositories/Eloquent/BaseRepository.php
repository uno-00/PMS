<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Eloquent repository implementation shared by every domain
 * repository in the system. Concrete repositories extend this class and
 * type-hint the model they manage, allowing custom query scopes to be
 * added on top of these CRUD primitives.
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected array $relations = [];

    public function __construct(protected Model $model) {}

    public function query(): Builder
    {
        return $this->model->newQuery()->with($this->relations);
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    public function find(string $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    public function findOrFail(string $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->query()->where($field, $value)->first($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function with(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }
}
