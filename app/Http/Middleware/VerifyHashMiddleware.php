<?php

namespace App\Http\Middleware;

use App\Contracts\Services\ErrorServiceInterface;
use App\Enums\ErrorsEnum;
use App\Enums\ResponseStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class VerifyHashMiddleware
{
    public function __construct(
        private ErrorServiceInterface $error,
    )
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle($request, Closure $next)
    {
        $hashFromRequest = $request->input('hash');
        $hashFromEnv = config('service-api.hash');

        if ($hashFromRequest !== $hashFromEnv) {
            $this->error->setError(ErrorsEnum::UNAUTHENTICATE);
            $response['status'] = ResponseStatusEnum::error->name;
            $response['data'] = $this->error->getToArray();
            $response['message'] = $this->error->getMessage();
            return response()->json($response, $this->error->getRequestCode());
        }

        return $next($request);
    }
}
