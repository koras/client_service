<?php

namespace App\Contracts\Repositories;

use App\Contracts\DTO\ArrayableDtoInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


interface RepositoryInterface
{
    public function first();

    public function all();

    public function existsById(int $id): bool;

    public function getExistingIds(array $needleIds): array;

    public function findInField(string $fieldName, array $values): Collection|array;

    public function find(int $id);

    public function findBy(array $params): Collection|array;

    public function findOneBy(array $params): ?Model;

    public function create(ArrayableDtoInterface $dto);

    public function update(int $id, array $params);

    public function delete(int $id);
}