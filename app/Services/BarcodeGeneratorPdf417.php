<?php

namespace App\Services;

use App\Contracts\Services\BarcodeGeneratorPdf417Interface;
use Le\PDF417\PDF417;

class BarcodeGeneratorPdf417 implements BarcodeGeneratorPdf417Interface
{
    private string $code;
    public function generate(string $data): BarcodeGeneratorPdf417Interface
    {
        $pdf417 = new PDF417();
        $pdf417->setColumns(4);
        $pdf417->setSecurityLevel(4);
        $pdf417->setForceBinary();

        $data = $pdf417->encode($data);

        $imageRenderer = new \Le\PDF417\Renderer\ImageRenderer([
            'format' => 'png',
            'quality' => 50,
            'scale' => 2,
            'ratio' => 1,
            'padding' => 0,
            'color' => '#000000',
            'bgColor' => '#ffffff',
        ]);

        $image = $imageRenderer->render($data);
        $this->code = base64_encode($image);

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
