<?php

namespace App\Exceptions;

use App\Contracts\Services\ErrorServiceInterface;
use App\Enums\ErrorsEnum;
use App\Enums\ResponseStatusEnum;
use App\Services\ErrorService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [

        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    /**
     * @var Application|mixed
     */
    private ErrorServiceInterface $error;

    public function __construct(Container $container)
    {
        $this->error = app(ErrorService::class);
        parent::__construct($container);
    }


    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception): Response|JsonResponse|HttpResponse|Application|ResponseFactory
    {

        // TODO: добавить логирование
        if ($this->shouldReport($exception)
            && (env('APP_ENV') == 'production' || env('APP_ENV') == 'dev')){
            try {
//                $handler = new ElasticsearchHandler(100, true);
//                $logger = new Logger('elastics', [$handler]);
//                $logger->debug($exception->getMessage(), context: [
//                    'exception' => $exception,
//                    'level' => Logger::ERROR,
//                    'line' => $exception->getLine(),
//                    'file' => $exception->getFile(),
//                    'message' => $exception->getMessage(),
//                    'trace' => $exception->getTrace(),
//                    'code' => $exception->getCode(),
//                ]);
            } catch (Throwable $e) {

            }
        }

        /* Filter the requests made on the API path */
//        if ($request->is('/*')) {
            if ($exception instanceof ValidationException) {
                $this->error->setError(ErrorsEnum::INVALID_FIELDS);
                $this->error->setFields($exception->errors());
            }

            if ($this->error->getCode()) {
                return new JsonResponse(['status' => ResponseStatusEnum::error->name, 'message' => $this->error->getMessage(), 'data' => $this->error->getToArray()],
                    $this->error->getRequestCode());
            } else {
                $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 405;
                $this->error->setError(ErrorsEnum::SYSTEM_ERROR, $exception->getMessage());
                return new JsonResponse(['status' => ResponseStatusEnum::error->name, 'message' => $this->error->getMessage(), 'data' => $this->error->getToArray()], $statusCode);
            }
//        }

        return parent::render($request, $exception);
    }
}
