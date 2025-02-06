<?php

namespace App\Contracts\Services;

use App\Http\Requests\GetWidgetInfoRequest;
use App\Http\Resources\WidgetInfoResource;

interface WidgetServiceInterface
{
    public function getWidgetInfo(GetWidgetInfoRequest $request, string $term = null): WidgetInfoResource;
}
