<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: ItemProvider::class)] private ProviderInterface $itemProvider,
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            dd($this->collectionProvider->provide($operation, $uriVariables, $context));
            return $this->collectionProvider->provide($operation, $uriVariables, $context);
        }

        $treasure = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$treasure instanceof DragonTreasure) {
            return $treasure;
        }

        $treasure->setIsOwnedByAuthenticatedUser($this->security->getUser() === $treasure->getOwner());

        return $treasure;
    }
}
