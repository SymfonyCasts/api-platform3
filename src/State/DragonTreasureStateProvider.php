<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DragonTreasure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: ItemProvider::class)] private ProviderInterface $itemProvider
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $treasure = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$treasure instanceof DragonTreasure) {
            return $treasure;
        }

        $treasure->setIsOwnedByAuthenticatedUser(true);

        return $treasure;
    }
}
