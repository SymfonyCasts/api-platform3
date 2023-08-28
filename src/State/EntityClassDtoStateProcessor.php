<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;

class EntityClassDtoStateProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository
    )
    {

    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        assert($data instanceof UserApi);

        $entity = $this->mapDtoToEntity($data);
        dd($entity);
    }

    private function mapDtoToEntity(object $dto): object
    {
        assert($dto instanceof UserApi);
        if ($dto->id) {
            $entity = $this->userRepository->find($dto->id);

            if (!$entity) {
                throw new \Exception(sprintf('Entity %d not found', $dto->id));
            }
        } else {
            $entity = new User();
        }

        $entity->setEmail($dto->email);
        $entity->setUsername($dto->username);
        $entity->setPassword('TODO properly');
        // TODO: handle dragon treasures

        return $entity;
    }
}
