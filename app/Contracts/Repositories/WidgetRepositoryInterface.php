<?php

namespace App\Contracts\Repositories;

use App\Contracts\Models\WidgetInterface;

interface WidgetRepositoryInterface
{
    public function findWidgetByDomainOrId(string $term): ?WidgetInterface;
}
