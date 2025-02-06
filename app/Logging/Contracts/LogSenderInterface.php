<?php

namespace App\Logging\Contracts;

interface LogSenderInterface
{
    public function send(string $message): void;
}