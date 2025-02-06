<?php

namespace App\DTO;

use Carbon\Carbon;
use Exception;
use Nette\Utils\DateTime;

readonly class CftCertificateInfoDto
{

    /**
     * @param int $date
     * @return string
     */
    private function dateFormat(int $date): string
    {
        $dateFormat = Carbon::createFromFormat('YmdHis', $date);
        return $dateFormat->format('Y-m-d H:i:s');
    }

    /**
     * @param int $amount
     * @return string
     */
    private function priceFormat(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }

    /**
     * @param string $expireAt
     * @param string $prepay
     * @return bool
     * @throws Exception
     */
    private function checkActive(string $expireAt, string $prepay): bool
    {
        $dateExpire = new DateTime($expireAt);
        $currentDate = new DateTime();

        return $dateExpire > $currentDate && $prepay > 0;
    }

    /**
     * @param array $dataResource
     * @param string $serialCertificate
     * @param string $widgetName
     * @return array
     * @throws Exception
     */
    public function CftCertificateInfoDtoByStrategy(array $dataResource, string $serialCertificate, string $widgetName): array
    {
        $response = $dataResource['response'];
        $items = $response['cardInfo']['items'];
        foreach ($items as $item) {
            switch ($item['name']) {
                case 'E_DATE';
                    $expireAt = $this->dateFormat($item['value']);
                    break;
                case 'PREPAY':
                    $prepay = $item['value'];
                    break;
            }
        }

        return [
            'widgetName' => $widgetName,
            'numberCertificate' => $serialCertificate,
            'expireAt' => $expireAt ?? '',
            'balance' => $this->priceFormat($prepay),
            'active' => $this->checkActive($expireAt, $prepay),
            'minChequePoints' => $response['pointsAllocation']['minChequePoints'],
            'maxChequePoints' => $response['pointsAllocation']['maxChequePoints'],
        ];
    }
}
