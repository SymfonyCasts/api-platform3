<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $innerProcessor,
        private Security $security
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data instanceof DragonTreasure && $data->getOwner() === null && $this->security->getUser()) {
            $data->setOwner($this->security->getUser());
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if ($data instanceof DragonTreasure) {
            $data->setIsOwnedByAuthenticatedUser($data->getOwner() === $this->security->getUser());
        }
    }
}
