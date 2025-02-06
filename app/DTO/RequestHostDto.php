<?php

namespace App\DTO;

use Illuminate\Http\Request;

readonly class RequestHostDto
{
    public function __construct(
        public string $httpHost,
        public string $host,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->getHttpHost(),
            $request->getHost(),
        );
    }
}