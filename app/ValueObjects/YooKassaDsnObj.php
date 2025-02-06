<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Stringable;

readonly class YooKassaDsnObj implements Arrayable, JsonSerializable, Stringable
{
    public function __construct(
        public string $login,
        public string $password,
    )
    {
    }

    public static function fromWidgetAttribute(string $yooKassaDsnAttribute): self
    {
        $ykassaDsn = [];
        preg_match('/(.*):(.*)/', $yooKassaDsnAttribute, $ykassaDsn);

        return new self(
            $ykassaDsn[1],
            $ykassaDsn[2]
        );
    }

    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->login . ':' . $this->password;
    }
}