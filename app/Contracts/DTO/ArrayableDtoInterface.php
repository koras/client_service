<?php

namespace App\Contracts\DTO;

interface ArrayableDtoInterface
{
    public function toArray(): array;
}