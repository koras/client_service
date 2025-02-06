<?php

namespace App\Contracts\Services;

use App\Contracts\Repositories\LifeCycleRepositoryInterface;

interface LifeCycleServiceInterface
{

    /**
     * @param int $orderId
     * @param string $system
     * @param int $status
     * @param string $value
     * @return void
     */
    public function createStatus(string $orderId, string $system, int $status, $value);



    public function history(string $orderId);
}

