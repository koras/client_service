<?php

namespace App\Traits;

use function Symfony\Component\String\u;

trait CleanDataTrait
{
    protected function cleanString(string $string): string
    {
        $string = u($string)->trim();
        $safeOutput = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        return $this->sanitizeString($safeOutput);
    }

    protected function sanitizeString(string $string): string
    {
        // Используем FILTER_SANITIZE_STRING
        return filter_var($string, FILTER_SANITIZE_STRING);
    }

    /**
     * Приводим номер телефона к формату +7хххххххххх
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function cleanPhone(string $phoneNumber): string
    {
        // Удаляем все нецифровые символы
        $formattedPhone = preg_replace('/\D/', '', $phoneNumber);

        // Берем только первые 11 символов, если их больше
        $formattedPhone = substr($formattedPhone, 0, 11);

        // Приведение номера к стандартному формату +7xxxxxxxxxx
        if (strlen($formattedPhone) === 10) {
            return '+7' . $formattedPhone;
        }

        if (strlen($formattedPhone) === 11 && (substr($formattedPhone, 0, 1) === '7' || substr($formattedPhone, 0, 1) === '8')) {
            return '+7' . substr($formattedPhone, 1);
        }

        return '';
    }
}