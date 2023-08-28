<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Entity\Notification;
use App\Repository\DragonTreasureRepository;
use Doctrine\ORM\EntityManagerInterface;

class DragonTreasureStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityClassDtoStateProcessor $innerProcessor,
        private EntityManagerInterface $entityManager,
        private DragonTreasureRepository $repository,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof DragonTreasureApi);
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $previousData = $context['previous_data'] ?? null;
        if ($previousData instanceof DragonTreasureApi
            && $data->isPublished
            && $previousData->isPublished !== $data->isPublished
        ) {
            $entity = $this->repository->find($data->id);
            $notification = new Notification();
            $notification->setDragonTreasure($entity);
            $notification->setMessage('Treasure has been published!');
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $result;
    }
}
