<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Entity\DragonTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\State\DailyQuestStateProcessor;
use App\State\DailyQuestStateProvider;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ApiResource(
    shortName: 'Quest',
    operations: [
        new GetCollection(),
        new Get(),
        new Patch(),

    ],
    paginationItemsPerPage: 10,
    provider: DailyQuestStateProvider::class,
    processor: DailyQuestStateProcessor::class,
)]
class DailyQuest
{
    #[Ignore]
    public \DateTimeInterface $day;
    public string $questName;
    public string $description;
    public int $difficultyLevel;
    public DailyQuestStatusEnum $status;
    public \DateTimeInterface $lastUpdated;
    #[ApiProperty(genId: false)]
    public QuestTreasure $treasure;

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
