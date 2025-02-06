<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Http\Requests\LifeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LifeCycleController extends Controller
{
    /**
     * @param LifeRequest $request
     * @param LifeCycleServiceInterface $cycleService
     * @return void
     */
    public function createStatus(LifeRequest $request, LifeCycleServiceInterface $cycleService)
    {
        $cycleService->createStatus($request->input('order_id'), $request->input('system'), $request->input('status'), $request->input('value',""));
    }

    /**
     * @param LifeRequest $request
     * @param LifeCycleServiceInterface $cycleService
     * @return void
     */
    public function getHistory(Request $request, LifeCycleServiceInterface $cycleService)
    {
        return $cycleService->history($request->input('order_id'));
    }
    /**
     * @param LifeRequest $request
     * @param LifeCycleServiceInterface $cycleService
     * @return void
     */
    public function getAll(Request $request, LifeCycleServiceInterface $cycleService)
    {
        return $cycleService->getAll();
    }


}
