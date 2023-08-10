<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserApiStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)] private ProviderInterface $itemProvider,
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

        $user = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$user) {
            return null;
        }

        return $this->mapEntityToDto($user);
    }

    private function mapEntityToDto(User $user): UserApi
    {
        $userApi = new UserApi();
        $userApi->id = $user->getId();
        $userApi->email = $user->getEmail();
        $userApi->username = $user->getUsername();
        $userApi->dragonTreasures = new ArrayCollection($user->getPublishedDragonTreasures()->getValues());
        $userApi->flameThrowingDistance = rand(1, 10);

        return $userApi;
    }
}
