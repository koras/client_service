<?php

namespace App\Logging;

use App\Logging\Contracts\LogObjectInterface;
use App\Logging\Contracts\LogSenderInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class LoggerWidgetHandler extends AbstractProcessingHandler
{
    private LogSenderInterface $logSender;
    private string $customHash;

    public function __construct(int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->logSender = app(LogSenderInterface::class);
        $this->customHash = $this->generateCustomHash();
    }

    public function write(LogRecord $record): void
    {
        $logObj = $this->getLogObject($record);
        if (!$logObj) {
            return;
        }

        $message = $this->generateMessage($logObj, $record->level);
        $this->logSender->send($message);
    }

    private function generateMessage(LogObjectInterface $logObject, Level $level): string
    {
        $now = (new Carbon())->format('Y-m-d H:i:s.v');
        $hash = $_SERVER['REQUEST_TIME'] ?? '' . getmypid();

        $data = $logObject->data;
        $data['priority'] = Str::lower($level->name);
        $data['server'] = config('logger-rabbit.nameServer');
        $data['dateTime'] = $now;

        $message = [
            'nameProject' => config('logger-rabbit.nameProject'),
            'method' => $logObject->method,
            'url' => config('logger-rabbit.nameServer'),
            'params' => $logObject->params,
            'data' => $data,
            'className' => $logObject->className,
            'functionName' => $logObject->functionName,
            'dateTime' => $now,
            'customHash' => $this->customHash,
            'hash' => $hash,
            'typeStorageTime' => $logObject->typeStorageTime,
        ];

        return json_encode($message);
    }

    private function getLogObject(LogRecord $record): ?LogObjectInterface
    {
        $context = $record->context;
        if (!empty($context['logObject']) && ($context['logObject'] instanceof WidgetLogObject)) {
            return $context['logObject'];
        }

        return null;
    }

    /**
     * @return string
     */
    private function generateCustomHash(): string
    {
        return md5(date('Y-m-d-H:i:s-') . microtime(true));
    }

}