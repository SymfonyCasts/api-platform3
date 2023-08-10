<?php

namespace App\Automapper;

use App\ApiResource\UserApi;
use App\Entity\User;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;

class UserApiToUserMapperConfiguration implements MapperConfigurationInterface
{
    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        dd($metadata->getPropertiesMapping());
    }

    public function getSource(): string
    {
        return UserApi::class;
    }

    public function getTarget(): string
    {
        return User::class;
    }
}
