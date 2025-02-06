<?php

namespace App\Utils;

class CharityCardsList
{
    private int $index = 0;
    private string $counterFile = '';
    private array $cards = [];

    public function __construct(string $counterFile, array $cards)
    {
        if(!is_file($counterFile)) {
            touch($counterFile);
        }
        if(sizeof($cards) === 0) {
            throw new \InvalidArgumentException('Empty array of cards.');
        }
        $this->counterFile = $counterFile;
        $this->cards = $cards;
        return $this;
    }

    public function getCard()
    {
        $this->index = (int) file_get_contents($this->counterFile);
        $this->index++;
        if($this->index >= sizeof($this->cards)) {
            $this->index = 0;
        }
        $f = fopen($this->counterFile, 'w+');
        fputs($f, $this->index);
        fclose($f);
        return $this->cards[$this->index];
    }
}