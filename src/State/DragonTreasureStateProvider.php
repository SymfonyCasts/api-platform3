<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')] private ProviderInterface $itemProvider,
        private Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        dd($operation);
        $treasure = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$treasure instanceof DragonTreasure) {
            return $treasure;
        }

        $treasure->setIsOwnedByAuthenticatedUser($this->security->getUser() === $treasure->getOwner());

        return $treasure;
    }
}
