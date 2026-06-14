<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    abstract protected function model(): string;

    public function query(): Builder
    {
        return $this->model()::query();
    }

    public function newModel(): Model
    {
        $class = $this->model();

        return new $class;
    }

    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function paginate(SearchFilterDto $dto, ?Builder $query = null): LengthAwarePaginator
    {
        $query ??= $this->query();

        if (method_exists($this->newModel(), 'scopeSearch')) {
            $query->search($dto->search);
        }
        if (method_exists($this->newModel(), 'scopeSort')) {
            $query->sort($dto->sort, $dto->direction);
        }

        return $query->paginate(perPage: $dto->perPage, page: $dto->page)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        $model->fill($data)->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function restore(Model $model): bool
    {
        return method_exists($model, 'restore') ? (bool) $model->restore() : false;
    }

    public function forceDelete(Model $model): bool
    {
        return (bool) $model->forceDelete();
    }
}
