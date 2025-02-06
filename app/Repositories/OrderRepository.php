<?php

namespace App\Repositories;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\DTO\ArrayableDtoInterface;
use App\Enums\OrderPaymentStatusEnum;
use App\ValueObjects\OrderPaymentObj;
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
class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    /**
     * @param OrderInterface $order
     * @param OrderPaymentObj $paymentObj
     * @return void
     */
    public function updatePaymentData(OrderInterface $order, OrderPaymentObj $paymentObj): void
    {
        $updateData = ['payment' => $paymentObj];
        $this->update($order->id, $updateData);
    }

    /**
     * @param OrderInterface $order
     * @param OrderPaymentStatusEnum $statusEnum
     * @return void
     */
    public function updatePaymentStatus(OrderInterface $order, OrderPaymentStatusEnum $statusEnum): void
    {
        $updateData = ['payment_status' => $statusEnum];
        $this->update($order->id, $updateData);
    }

    /**
     * Найти заказ по id или tracking_number
     *
     * @param string $identificator
     * @return OrderInterface|null
     */
    public function findByIdOrTrackingNumber(string $identificator): ?OrderInterface
    {
        // TODO: переписать
        return $this->model
            ->where('id', $identificator)
            ->orWhere('tracking_number', $identificator)
            ->first();
    }

    /**
     * Найти заказ по tracking_number
     *
     * @param string $trackingNumber
     * @return OrderInterface|null
     */
    public function findByTrackingNumber(string $trackingNumber): ?OrderInterface
    {
        return $this->model
            ->where('tracking_number', $trackingNumber)
            ->first();
    }

}
