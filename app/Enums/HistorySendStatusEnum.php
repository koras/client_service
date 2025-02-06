<?php

namespace App\Enums;

enum HistorySendStatusEnum: int
{
    case Error = 0;
    case SentToService = 1;
    case Success = 2;
}
