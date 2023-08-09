<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
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
        if ($operation instanceof CollectionOperationInterface) {
            $users = $this->collectionProvider->provide($operation, $uriVariables, $context);
            assert($users instanceof Paginator);

            $userDtos = [];
            foreach ($users as $user) {
                $userDtos[] = $this->mapEntityToDto($user);
            }

            return new TraversablePaginator(
                new \ArrayIterator($userDtos),
                $users->getCurrentPage(),
                $users->getItemsPerPage(),
                $users->getTotalItems()
            );
        }

        dd($uriVariables);
    }

    private function mapEntityToDto(User $user): UserApi
    {
        $userApi = new UserApi($user->getId());
        $userApi->email = $user->getEmail();
        $userApi->username = $user->getUsername();
        $userApi->dragonTreasures = $user->getDragonTreasures();
        $userApi->flameThrowingDistance = rand(1, 10);

        return $userApi;
    }
}
