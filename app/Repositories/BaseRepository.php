<?php

namespace App\Repositories;

use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Repositories\RepositoryInterface;
use App\Traits\SnakeCasingTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


class BaseRepository implements RepositoryInterface
{
    use SnakeCasingTrait;

    public function __construct(
        protected Model $model
    )
    {
    }

    public function first()
    {
        return $this->model->first();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function existsById(int|string $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function getExistingIds(array $needleIds): array
    {
        return $this->model->whereIn('id', $needleIds)->pluck('id')->toArray();
    }

    public function findInField(string $fieldName, array $values): Collection|array
    {
        return $this->model->whereIn($fieldName, $values)->get();
    }

    public function find(int|string $id)
    {
        return $this->model->findOrFail($id);
    }

    public function findBy(array $params): Collection|array
    {
        $query = $this->model->newQuery();
        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get();
    }

    public function findOneBy(array $params): ?Model
    {
        $query = $this->model->newQuery();
        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    public function create(ArrayableDtoInterface $dto)
    {
        $dataArray = $dto->toArray();
        $dataToModel = $this->arrayKeysToSnakeCase($dataArray);
        return $this->model->create($dataToModel);
    }

    public function update(int|string $id, array $params)
    {
        $dataToModel = $this->arrayKeysToSnakeCase($params);

        $record = $this->find($id);
        $record->update($dataToModel);
        return $record;
    }

    public function delete(int|string $id)
    {
        $record = $this->find($id);
        return $record->delete();
    }
}
