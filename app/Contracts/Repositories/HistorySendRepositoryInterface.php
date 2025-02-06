<?php

namespace App\Contracts\Repositories;

interface HistorySendRepositoryInterface
{
    public function getSendsPinSms($order_item_id);
}
