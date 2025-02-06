<?php

namespace App\DTO;

use App\Contracts\Models\CertificateInterface;

readonly class ShowCertificateDataDto
{
    public function __construct(
        public CertificateInterface $certificate,
        public string $cover,
        public ?string $qr,
        public ?string $barcode,
        public ?string $barcodePdf417,
    )
    {
    }
}
