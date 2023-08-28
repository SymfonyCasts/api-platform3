<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\DragonTreasureRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasureApi::class, to: DragonTreasure::class)]
class DragonTreasureApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private DragonTreasureRepository $repository,
        private Security $security,
        private MicroMapperInterface $microMapper,
    )
    {

    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof DragonTreasureApi);

        $entity = $dto->id ? $this->repository->find($dto->id) : new DragonTreasure($dto->name);
        if (!$entity) {
            throw new \Exception('DragonTreasure not found');
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        $entity = $to;
        assert($dto instanceof DragonTreasureApi);
        assert($entity instanceof DragonTreasure);

        if ($dto->owner) {
            $entity->setOwner($this->microMapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]));
        } else {
            $entity->setOwner($this->security->getUser());
        }

        $entity->setDescription($dto->description);
        $entity->setCoolFactor($dto->coolFactor);
        $entity->setValue($dto->value);
        $entity->setIsPublished($dto->isPublished);

        return $entity;
    }
}
