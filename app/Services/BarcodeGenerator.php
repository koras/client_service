<?php

namespace App\Services;

use App\Contracts\Services\BarcodeGeneratorInterface;
use Milon\Barcode\DNS1D;

class BarcodeGenerator implements BarcodeGeneratorInterface
{
    private string $code;
    public function generate(string $data): BarcodeGeneratorInterface
    {
        $this->code = DNS1D::getBarcodePNG($data, 'C128', 3, 80);
        return $this;
    }

    public function getBase64(): ?string
    {
        if (empty($this->code)) {
            return null;
        }

        return 'data:image/png;base64,' . $this->code;
    }
}