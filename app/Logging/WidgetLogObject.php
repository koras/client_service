<?php

namespace App\Logging;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Logging\Contracts\LogObjectInterface;

readonly class WidgetLogObject implements LogObjectInterface
{
    public function __construct(
        public string $message,
        public string $method,
        public array $params,
        public array $data,
        public string $className,
        public string $functionName,
        public string $customHash,
        public string $typeStorageTime
    )
    {
    }

    public static function make(
        string $message,
        string $name,
        OrderInterface|OrderItemInterface|CertificateInterface|null $entity = null,
        string $orderId = null,
        string $type = self::TYPE_IN,
        array $params = []
    ): self
    {
        $debugInfo = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1];

        $functionName = $debugInfo['function'];
        $classShortName = self::getShortClassName($debugInfo['class']);
        $methodName = $classShortName . '.' . $functionName;
        $typeStorageTime = config('logger-rabbit.timeStorage.short');


        $data['name'] = $name;
        $data['type'] = $type;
        $data['msg'] = $message;

        $dataEntity = self::dataFromEntity($entity);
        $data = array_merge($data, $dataEntity);

        if (!is_null($orderId) && !isset($data['widgetOrderID'])) {
            $data['widgetOrderID'] = $orderId;
        }

        return new self(
            message: $message,
            method: $methodName,
            params: $params,
            data: $data,
            className: $classShortName,
            functionName: $functionName,
            customHash: '',
            typeStorageTime: $typeStorageTime
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'method' => $this->method,
            'params' => $this->params,
            'data' => $this->data,
            'className' => $this->className,
            'functionName' => $this->functionName,
            'customHash' => $this->customHash,
            'typeStorageTime' => $this->typeStorageTime,
        ];
    }

    public function toContext(): array
    {
        return [
            'logObject' => $this
        ];
    }

    private static function dataFromEntity(OrderInterface|OrderItemInterface|CertificateInterface|null $entity = null): array
    {

        if ($entity instanceof CertificateInterface) {
            return self::dataFromCertificate($entity);
        }

        if ($entity instanceof OrderItemInterface) {
            return self::dataFromOrderItem($entity);
        }

        if ($entity instanceof OrderInterface) {
            return self::dataFromOrder($entity);
        }

        return [];
    }

    private static function dataFromOrder(OrderInterface $order): array
    {
        $data['widgetID'] = $order->widget->id;
        $data['widgetOrderID'] = $order->id;
        return $data;
    }

    private static function dataFromOrderItem(OrderItemInterface $orderItem): array
    {
        $data['widgetID'] = $orderItem->order->widget->id;
        $data['widgetOrderID'] = $orderItem->order->id;
        $data['orderID'] = $orderItem->tiberium_order_id;
        return $data;
    }

    private static function dataFromCertificate(CertificateInterface $certificate): array
    {
        $data['widgetID'] = $certificate->orderItem->order->widget->id;
        $data['widgetOrderID'] = $certificate->orderItem->order->id;
        $data['orderID'] = $certificate->orderItem->tiberium_order_id;
        $data['certificateID'] = $certificate->id;
        return $data;
    }

    private static function getShortClassName(string $className): string
    {
        return substr(strrchr($className, "\\"), 1);
    }
}