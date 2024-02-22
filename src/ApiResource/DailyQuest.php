<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use App\State\DailyQuestStateProvider;

#[ApiResource(
    shortName: 'Quest',
    provider: DailyQuestStateProvider::class,
)]
class DailyQuest
{
    public \DateTimeInterface $day;

    public function __construct(\DateTimeInterface $day)
    {
        $this->day = $day;
    }
}
