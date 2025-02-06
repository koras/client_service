<?php

namespace App\Helpers;

class Helper
{
    public static function isArrayOfArrays(?array $array): bool
    {
        if (!is_array($array)) {
            return false; // Если не массив, то не является массивом массивов
        }

        foreach ($array as $element) {
            if (!is_array($element)) {
                return false; // Если хотя бы один элемент не является массивом, то не является массивом массивов
            }
        }

        return true; // Если все элементы являются массивами, то это массив массивов
    }
}