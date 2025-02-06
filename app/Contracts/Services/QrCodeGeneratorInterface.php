<?php

namespace App\Contracts\Services;

interface QrCodeGeneratorInterface
{
    public function generate(string $data): self;

    public function getBase64(): ?string;

}
