<?php

namespace App\Helpers;

class TiberiumHelper
{

    public static function getProductFromTiberiumData(array $data): array|null
    {
        return $data['Products']['Product'] ?? null;
    }

    public static function getProductPriceFromData(?array $productData): int
    {
        $price = 0;
        if (is_null($productData)) {
            return $price;
        }
//        $product = self::getProductFromTiberiumData($data);

        if (isset($productData['Price'])){
            $price = $productData['Price'];
        }
        return $price;
    }

    public static function getProductNominalFromData(?array $productData): array
    {
        $result = [
            'nominal' => 0,
            'currency' => null,
        ];
        if (is_null($productData)) {
            return $result;
        }
//        $product = self::getProductFromTiberiumData($productData);
        if(!is_null($productData) && isset($productData['Params']['Param']) && count($productData['Params']['Param'])){
            $params = $productData['Params']['Param'] ?? null;
            if (is_null($params)){
                return $result;
            }
            foreach($params as $row){
                if($row['name']['#'] == 'Номинал'){
                    if(is_array($row['value'])){
                        $result['nominal'] = $row['value']['#'] ?? null;
                        $result['currency'] = $row['value']['@unit'] ?? null;
                    }else{
                        $result['nominal'] = $productData['Price'] ?? null;
                        $result['currency'] = 'руб.';
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Тибериум возвращает разные форматы ответа - если запрос по 1 id товара, то возвращается массив значений с параметрами этого товара
     * если в запросе несколько id товаров, то возвращается массив с массивами значений прарметров товаров
     * Обрабатываем ответ тибериума - форматируем его для дальнейшей обработки.
     * Даже если выполнялся запрос по 1 товару, далее обрабатываем массив массивов параметров
     *
     * @param array|null $productsData
     * @return array|array[]|null[]|null
     */
    public static function prepareProductsDataAsArray(?array $productsData): ?array
    {
        if (Helper::isArrayOfArrays($productsData)) {
            return $productsData;
        }

        return [$productsData];
    }

}