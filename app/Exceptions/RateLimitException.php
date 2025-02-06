<?php

namespace App\Exceptions;

use Exception;

/**
 *
 */
class RateLimitException extends Exception
{
    /**
     * Код ошибки
     */
    private const int CODE_ERROR  = 429;

    /**
     *
     */
    private const string MESSAGE_ERROR = 'Rate limit exceeded';

    /**
     * @param $message
     * @param $code
     */
    public function __construct($message = self::MESSAGE_ERROR, $code = self::CODE_ERROR)
    {
        parent::__construct($message, $code);
    }
}
