<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserApiStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider
    )
    {

    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $users = $this->collectionProvider->provide($operation, $uriVariables, $context);

        $userDtos = [];
        foreach ($users as $user) {
            $userDtos[] = $this->mapEntityToDto($user);
        }

        return $userDtos;
    }

    private function mapEntityToDto(User $user): UserApi
    {
        $userApi = new UserApi($user->getId());
        $userApi->email = $user->getEmail();
        $userApi->username = $user->getUsername();
        $userApi->dragonTreasures = $user->getDragonTreasures();

        return $userApi;
    }
}
