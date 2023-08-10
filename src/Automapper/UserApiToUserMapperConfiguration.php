<?php

namespace App\Automapper;

use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;

class UserApiToUserMapperConfiguration implements MapperConfigurationInterface
{
    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        // TODO: Implement process() method.
    }

    public function getSource(): string
    {
        // TODO: Implement getSource() method.
    }

    public function getTarget(): string
    {
        // TODO: Implement getTarget() method.
    }
}
