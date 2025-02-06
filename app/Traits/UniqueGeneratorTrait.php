<?php

namespace App\Traits;

trait UniqueGeneratorTrait
{
    /**
     * Сгенерировать уникальный номер заказа для ПЦ
     * @return int
     */
    protected function generateUniqueExternalOrderId(): int
    {
        return round(microtime(true) * 1000) + rand(1000,9999);
    }

    /**
     * Сгенерировать уникальный номер заказа в формате хххх-хххх
     * человекочитаемый номер
     *
     * @return string
     */
    protected function generateUniqueTrackingNumber(): string
    {
        // Получаем текущее время в микросекундах
        $microtime = microtime(true);

        // Разделяем на целую и дробную части
        list($usec, $sec) = explode(".", $microtime);

        // Берем последние 4 цифры от времени в секундах
        $part1 = substr($sec, -4);

        // Генерируем случайные 4 цифры
        $randomNumber = mt_rand(1000, 9999);

        // Объединяем части в формат xxxx-xxxx
        return str_pad($part1, 4, '0', STR_PAD_LEFT) . '-' . str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
    }

}