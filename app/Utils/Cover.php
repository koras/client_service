<?php

namespace App\Utils;

use Symfony\Component\Validator\Constraints as Assert;

class Cover
{
    /**
     * @Assert\NotBlank()
     */
    private $filename;

    /**
     * @Assert\PositiveOrZero
     */
    private $sortOrder;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
