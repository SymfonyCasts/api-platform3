<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\State\DailyQuestStateProvider;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ApiResource(
    shortName: 'Quest',
    provider: DailyQuestStateProvider::class,
)]
class DailyQuest
{
    #[Ignore]
    public \DateTimeInterface $day;

    public function __construct(\DateTimeInterface $day)
    {
        $this->day = $day;
    }

    #[ApiProperty(readable: false, identifier: true)]
    public function getDayString(): string
    {
        return $this->day->format('Y-m-d');
    }
}
