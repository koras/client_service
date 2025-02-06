<?php

namespace App\Services;

use App\Contracts\Services\ValidationServiceInterface;
use Illuminate\Support\Facades\Validator;

class ValidationService implements ValidationServiceInterface
{
    private const string PHONE_PATTERN = '/^(\+7|8|7)[\s\(]*\d{3}[\s\)]*\d{3}[\s-]*\d{2}[\s-]*\d{2}$/';

    /**
     * @param string $phoneNumber
     * @return string|null
     */
    public function validatePhoneNumber(string $phoneNumber): ?string
    {
        $validator = Validator::make(
            ['phone_number' => $phoneNumber],
            ['phone_number' => ['required', 'regex:' . self::PHONE_PATTERN]]
        );

        if ($validator->fails()) {
            return $validator->errors()->first('phone_number');
        }

        return null;
    }

    /**
     * @param string $email
     * @return string|null
     */
    public function validateEmail(string $email): ?string
    {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            return $validator->errors()->first('email');
        }

        return null;
    }

    /**
     * @param string $string
     * @param int $maxLength
     * @return string|null
     */
    public function validateString(string $string, int $maxLength = 255): ?string
    {
        $validator = Validator::make(
            ['string' => $string],
            ['string' => 'required|max:' . $maxLength]
        );

        if ($validator->fails()) {
            return $validator->errors()->first('string');
        }

        return null;
    }
}