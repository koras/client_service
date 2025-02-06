<?php

namespace App\Logging\Contracts;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;

interface LogObjectInterface
{
    public const string TYPE_IN = 'in';
    public const string TYPE_OUT = 'out';

    public static function make(
        string $message,
        string $name,
        OrderInterface|OrderItemInterface|CertificateInterface|null $entity = null,
        string $orderId = null,
        string $type = self::TYPE_IN,
        array $params = []
    ): self;

    public function toArray(): array;

    public function toContext(): array;
}