<?php

namespace App\Contracts\Services;

use App\Enums\ErrorsEnum;

interface ErrorServiceInterface
{
    public function getCode(): ?string;

    public function getRequestCode(): ?int;

    public function getFields(): ?array;

    public function setFields(array $fields): void;

    public function setError(ErrorsEnum $errorsEnum, string $message = ''): self;

    public function getToArray(): array;

    public function exist(): bool;

    public function abort(): void;

    public function getMessage(): ?string;
}