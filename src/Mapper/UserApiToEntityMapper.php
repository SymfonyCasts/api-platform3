<?php

namespace App\Mapper;

use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: UserApi::class, to: User::class)]
class UserApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private MicroMapperInterface $microMapper,
        private PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserApi);

        $userEntity = $dto->id ? $this->userRepository->find($dto->id) : new User();
        if (!$userEntity) {
            throw new \Exception('User not found');
        }

        return $userEntity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserApi);
        $entity = $to;
        assert($entity instanceof User);

        $entity->setEmail($dto->email);
        $entity->setUsername($dto->username);
        if ($dto->password) {
            $entity->setPassword($this->userPasswordHasher->hashPassword($entity, $dto->password));
        }

        $dragonTreasureEntities = [];
        foreach ($dto->dragonTreasures as $dragonTreasureApi) {
            $dragonTreasureEntities[] = $this->microMapper->map($dragonTreasureApi, DragonTreasure::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }
        $this->propertyAccessor->setValue($entity, 'dragonTreasures', $dragonTreasureEntities);

        return $entity;
    }
}
