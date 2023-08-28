<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\State\DailyQuestStateProvider;

#[ApiResource(
    shortName: 'Quest',
    provider: DailyQuestStateProvider::class,
)]
class DailyQuest
{
    #[ApiProperty(identifier: true)]
    public \DateTimeInterface $day;

    public function __construct(\DateTimeInterface $day)
    {
        $this->day = $day;
    }
}
