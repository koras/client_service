<?php

namespace App\Repositories;
use App\Contracts\Repositories\HistorySendRepositoryInterface;
use App\Contracts\DTO\ArrayableDtoInterface;
use App\Models\HistorySend;
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
 * @method Model update(int|string $id, array $params)
 * @method bool delete(int|string $id)
 */
class HistorySendRepository extends BaseRepository implements HistorySendRepositoryInterface
{
    public function create(ArrayableDtoInterface $dto)
    {
        $dataArray = $dto->toArray();
        $dataToModel = $this->arrayKeysToSnakeCase($dataArray);
        return $this->model->create($dataToModel);
    }

    public function getSendsPinSms($certificate_id)
    {
        return HistorySend::where('certificate_id', $certificate_id)
            ->get();
    }
}
