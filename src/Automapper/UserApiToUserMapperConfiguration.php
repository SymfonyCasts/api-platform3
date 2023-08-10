<?php

namespace App\Automapper;

use App\ApiResource\UserApi;
use App\Entity\User;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;

class UserApiToUserMapperConfiguration implements MapperConfigurationInterface
{
    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        assert($metadata instanceof MapperMetadata);

        $metadata->forMember('password', function (UserApi $userApi) {
            return 'BAAAR';
        });
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
