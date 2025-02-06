<?php

namespace App\Services;

use App\Contracts\Services\ErrorServiceInterface;
use App\Enums\ErrorsEnum;

class ErrorService implements ErrorServiceInterface
{
    private ?string $code;
    private ?string $message;
    private ?int $requestCode;
    private ?array $fields;

    public function __construct()
    {
        $this->code = null;
        $this->message = null;
        $this->fields = null;
    }

    /**
     * @return int|null
     */
    public function getRequestCode(): ?int
    {
        return $this->requestCode;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @param ErrorsEnum $errorsEnum
     * @param string $message
     * @return $this
     */
    public function setError(ErrorsEnum $errorsEnum, string $message = ''): self
    {
        $this->code = $errorsEnum->name;

        if(empty($message)) {
            $message = $errorsEnum->value;
        }
        $this->message = $message;
        $this->requestCode = $errorsEnum->getRequestCode();

        return $this;
    }

    /**
     * @return array
     */
    public function getToArray(): array
    {
        if(!is_null($this->code) || !is_null($this->message)) {
            return [
                'code' => $this->code,
                'message' => $this->message,
                'fields' => $this->fields
            ];
        }

        return [];
    }

    /**
     * @return bool
     */
    public function exist(): bool
    {
        if(!empty($this->code) && !empty($this->message)) {
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    public function abort(): void
    {
        abort($this->requestCode);
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

}