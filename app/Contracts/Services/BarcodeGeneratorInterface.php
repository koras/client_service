<?php

namespace App\Contracts\Services;

interface BarcodeGeneratorInterface
{
    public function generate(string $data): self;

    public function getBase64(): ?string;
}