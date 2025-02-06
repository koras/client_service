<?php

namespace App\Contracts\Services;

use App\DTO\CommitOrderPCDto;
use App\DTO\FlexCommitOrderPCDto;

interface PCApiServiceInterface
{
    public function getCatalog(): ?array;

    public function getRemains(): ?array;

    public function commitOrder(CommitOrderPCDto $orderPCDto): ?array;

    public function flexCommitOrder(FlexCommitOrderPCDto $orderPCDto): ?array;

    public function getCftCertificateInfo(int $supplierId, string $serialCertificate);
}
