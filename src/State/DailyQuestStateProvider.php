<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\ApiResource\QuestTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\Repository\DragonTreasureRepository;

class DailyQuestStateProvider implements ProviderInterface
{
    public function __construct(
        private DragonTreasureRepository $treasureRepository,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->createQuests();
        }

        $quests = $this->createQuests();

        return $quests[$uriVariables['dayString']] ?? null;
    }

    private function createQuests(): array
    {
        $treasures = $this->treasureRepository->findBy([], [], 10);

        $quests = [];
        for ($i = 0; $i < 50; $i++) {
            $quest = new DailyQuest(new \DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->questName = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficultyLevel = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new \DateTimeImmutable(sprintf('- %d days', rand(10, 100)));

            $randomTreasure = $treasures[array_rand($treasures)];
            $quest->treasure = new QuestTreasure(
                $randomTreasure->getName(),
                $randomTreasure->getValue(),
                $randomTreasure->getCoolFactor(),
            );

            $quests[$quest->getDayString()] = $quest;
        }

        return $quests;
    }
}
