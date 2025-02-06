<?php

namespace App\Repositories;

use App\Contracts\Repositories\WidgetUserRepositoryInterface;
use App\Contracts\DTO\ArrayableDtoInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class BaseRepository
 * @package App\Repositories
 *
 * @method Collection|Model[] all()
 * @method bool existsById(int|string $id)
 * @method array getExistingIds(array $needleIds)
 * @method Collection|array findInField(string $fieldName, array $values)
 * @method Model find(int|string $id)
 * @method Collection|array findBy(array $params)
 * @method Model|null findOneBy(array $params)
 * @method Model create(ArrayableDtoInterface $dto)
 * @method Model update(int|string $id, array $params)
 * @method bool delete(int|string $id)
 */
class WidgetUserRepository extends BaseRepository implements WidgetUserRepositoryInterface
{

}
