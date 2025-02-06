<?php

namespace App\Services;

use App\Contracts\Services\QrCodeGeneratorInterface;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeGenerator implements QrCodeGeneratorInterface
{
    private string $code;

    public function generate(string $data): QrCodeGeneratorInterface
    {
        $this->code = QrCode::format('png')
            ->size('130')
            ->generate($data);

        return $this;
    }

    public function getBase64(): ?string
    {
        if (empty($this->code)) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($this->code);
    }
}