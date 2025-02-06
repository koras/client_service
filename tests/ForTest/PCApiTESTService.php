<?php

namespace Tests\ForTest;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\PCApiServiceInterface;
use App\DTO\CommitOrderPCDto;

class PCApiTESTService implements PCApiServiceInterface
{

    public function setDsn(WidgetInterface $widget): PCApiServiceInterface
    {
        return $this;
    }

    public function getCatalog(): ?array
    {
        return null;
    }

    public function getRemains(): ?array
    {
        return null;
    }

    public function commitOrder(CommitOrderPCDto $orderPCDto): ?array
    {
        return [];
    }
}