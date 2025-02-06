<?php

namespace App\Services;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\LifeCycleDto;
use App\Contracts\Models\LifeCycleInterface;
use App\Contracts\Repositories\LifeCycleRepositoryInterface;
use App\Contracts\Services\LifeCycleServiceInterface;

class LifeCycleService implements LifeCycleServiceInterface
{
    const STATUS_LIFE = [
        1 => "Создали заказ",
        2 => "Сохраняем платёж, переходим к оплате",
        3 => "Переотправка заказа",
        4 => "Создание счёта в ykassa",
        5 => "Получить информацию о платеже по Id платежа",
        6 => "Получить объект Order по данным из платежа (по ID заказа)",
        7 => "Платёж отменён callback от Yookassa",
        8 => "Отправляем сообщение в очередь Обработки оплаченных заказов",
        9 => "createDtoFromOrderItem",
        10 => "Отправляем сертификат для подготовки в очередь",
        11 => "Отправляем сообщение в очередь обработки оплаченных заказов",
        12 => "ПереОтправляем сертификат для подготовки в очередь",
        13 => "ПереОтправляем сертификат для подготовки в очередь",
        14 => "Публикация заказа в ПЦ",
        15 => "На почту отправлен заказ",
        16 => "Подготавливаем данные для письма",
        17 => "Генерируем короткую(ие) ссылку(и)",
        18 => "Отправляем в очередь",
        19 => "Отправляем в очередь email",
        20 => "Отправляем в очередь sms",
        21 => "Проверили полученный сертификат из ПЦ",
        22 => "Нет сертификатов в ПЦ",
        23 => "Сертификаты получены из ПЦ",
        24 => "Пришёл статус от ПЦ",
        25 => "Отправляем клиенту ссылку на сертификат ",
        26 => "Клиент переходит к оплате заказа",
        27 => "Сертификат отправлен в смс в очередь",
        28 => "Сертификат отправлен в email в очередь",
        29 => " -- ",
        30 => " -- ",

    ];

    public function __construct(
        private LifeCycleRepositoryInterface $lifeCycle,
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository

    ) {
    }

    /**
     * @param string $orderId
     * @param string $system
     * @param int $status
     * @param string $value
     * @param LifeCycleRepositoryInterface $lifeCycle
     * @return void
     */
    public function createStatus(string $orderId, string $system, int $status, $value = "")
    {
        $this->lifeCycle->create(
            new LifeCycleDto(
                $orderId,
                $system,
                $status,
                $value,
            )
        );
    }

    /**
     * @param string $orderId
     * @param string $system
     * @param int $status
     * @param string $value
     * @param LifeCycleRepositoryInterface $lifeCycle
     */
    public function history(string $orderId)
    {
        // 060cfa62-3959-482e-bdaf-5a217dfe793b
        //1722873418231
        $pattern = '/([a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12})/i';
        preg_match($pattern, $orderId,$match);

        $widget = null;
        $order = null;
        if(isset($match[0])) {
            $tiberiumOrderId = $this->orderItemRepository->findBy(["widget_order_id" => $orderId]);
            $widget = $this->lifeCycle->findBy(["order_id" => $orderId]);
            $order = $this->orderRepository->find($orderId);
        }else{
            $tiberiumOrderId = $this->orderItemRepository->findBy(["tiberium_order_id" => $orderId]);
            if(isset($tiberiumOrderId[0])){
                $widget = $this->lifeCycle->findBy(["order_id" => $tiberiumOrderId[0]->widget_order_id]);
            }
            if(!$widget){
                return [
                    'error'=>true,
                    "text" => "нет истории по этому заказу",
                ];
            }

        }


        return [
            'error'=>false,
            "history" => $this->prepareData($widget),
            "order" => $order,
            "tiberiumOrderId" =>  $tiberiumOrderId,
            "status" => self::STATUS_LIFE,
        ];
    }
    /**
     * @param string $orderId
     * @param string $system
     * @param int $status
     * @param string $value
     * @param LifeCycleRepositoryInterface $lifeCycle
     */
    public function getAll()
    {
        return [
            "status" => self::STATUS_LIFE,
            "order" => $this->orderRepository->find()
        ];
    }


    private function prepareData($data)
    {
        $result = [];
        foreach ($data as $row) {
            $result[] = [
                "name" => self::STATUS_LIFE[$row->status],
                "status" => $row->status,
                "value" => $row->value,
                "created_at" => $row->created_at->format('H:i:s d-m-Y'),
            ];
        }
        return $result;
    }

}
