<?php

namespace App\Contracts\Services;

interface BarcodeGeneratorPdf417Interface
{
    public function generate(string $data): self;

    public function getBase64(): ?string;
}
