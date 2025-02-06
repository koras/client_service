<?php

namespace App\Contracts\Services;

use Illuminate\Support\Facades\Validator;

interface ValidationServiceInterface
{
    /**
     * @param string $phoneNumber
     * @return string|null
     */
    public function validatePhoneNumber(string $phoneNumber): ?string;

    /**
     * @param string $email
     * @return string|null
     */
    public function validateEmail(string $email): ?string;

    /**
     * @param string $string
     * @param int $maxLength
     * @return string|null
     */
    public function validateString(string $string, int $maxLength = 255): ?string;
}