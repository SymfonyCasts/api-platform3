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
        /** @var iterable<User> $users */
        $users = $this->collectionProvider->provide($operation, $uriVariables, $context);

        $userApis = [];
        foreach ($users as $user) {
            $userApi = new UserApi();

            // works
            $userApi->id = $user;

            // will not work
            // change line also in UserApi
            //$userApi->id = $user->getId();

            $userApi->email = $user->getEmail();
            $userApi->username = $user->getUsername();
            $userApi->dragonTreasures = $user->getDragonTreasures();
            $userApi->foo = rand();

            $userApis[] = $userApi;
        }

        return $userApis;
    }
}
