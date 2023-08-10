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
use Jane\Component\AutoMapper\AutoMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserApiStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)] private ProviderInterface $itemProvider,
        private AutoMapperInterface $autoMapper
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
        return $this->autoMapper->map($user, new UserApi());
    }
}
