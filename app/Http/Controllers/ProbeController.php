<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Services\QueueProducer;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use Throwable;

class ProbeController
{

    /**
     * @OA\Get(
     *     path="/health",
     *     summary="Запрос проверки статуса доступности приложения",
     *     tags={"Probe"},
     *     @OA\Response(
     *         response=200,
     *         description="Статус запроса - успешно выполнен",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function read(WidgetRepositoryInterface $widgetRepository): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $widget = $widgetRepository->first();
        } catch (Throwable|Error $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        if (!($widget instanceof Model)) {
            return response()->json(['status' => 'error', 'message' => 'DB get model error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    public function version(): JsonResponse
    {
        return response()->json(['version' => '0.30']);
    }

    /**
     * @throws Exception
     */
    public function queues(QueueProducer $queueProducer): void
    {
        $queueProducer->publishProbeQueue('probe', 10);
    }

    /**
     * @throws Exception
     */
    public function cache(): mixed
    {
        return Cache::remember('test_cache', 60, function (){
            return date('d.y.m H:i:s');
        });
    }

}
