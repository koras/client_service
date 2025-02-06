<?php

namespace App\Repositories;

use App\Contracts\Models\SystemSettingInterface;
use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Repositories\SystemSettingRepositoryInterface;
use App\Enums\SettingSectionEnum;
use Illuminate\Support\Collection;

/**
 * Class BaseRepository
 * @package App\Repositories
 *
 * @method Collection|SystemSettingInterface[] all()
 * @method bool existsById(int|string $id)
 * @method array getExistingIds(array $needleIds)
 * @method Collection|array findInField(string $fieldName, array $values)
 * @method SystemSettingInterface find(int|string $id)
 * @method Collection|array findBy(array $params)
 * @method SystemSettingInterface|null findOneBy(array $params)
 * @method SystemSettingInterface create(ArrayableDtoInterface $dto)
 * @method SystemSettingInterface update(int|string $id, array $params)
 * @method bool delete(int|string $id)
 */
class SystemSettingRepository extends BaseRepository implements SystemSettingRepositoryInterface
{
    public function getSettingsBySection(SettingSectionEnum $sectionEnum): Collection|array
    {
        return $this->model->where('section', $sectionEnum->value)->get();
    }

}
