<?php

namespace App\Contracts\Services\Bitrix24;

use App\DTO\BitrixCreateTaskDto;

interface BitrixApiServiceInterface
{
    public function sendTask(BitrixCreateTaskDto $taskDto): array;

}
