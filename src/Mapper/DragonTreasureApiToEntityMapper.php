<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Repository\DragonTreasureRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: DragonTreasureApi::class, to: DragonTreasure::class)]
class DragonTreasureApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private DragonTreasureRepository $repository,
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

        // TODO owner if needed
        // TODO and other fields

        return $entity;
    }
}
