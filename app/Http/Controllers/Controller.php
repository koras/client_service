<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ErrorServiceInterface;
use App\Enums\ResponseStatusEnum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    protected array $response;

    /**
     * @OA\Info(title="WidgetClientBackService API", version="0.1")
     */
    public function __construct(
        protected ErrorServiceInterface $error,
    )
    {
        $this->response = [
            'status' => ResponseStatusEnum::ok->name,
            'message' => null,
            'data' => null,
        ];
    }

}
