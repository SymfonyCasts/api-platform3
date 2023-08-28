<?php

namespace App\ApiResource;

class QuestTreasure
{
    public function __construct(
        public string $name,
        public int $value,
        public int $coolFactor,
    )
    {
    }
}
