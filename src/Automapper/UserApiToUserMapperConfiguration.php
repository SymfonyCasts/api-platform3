<?php

namespace App\Automapper;

use App\ApiResource\UserApi;
use App\Entity\User;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserApiToUserMapperConfiguration implements MapperConfigurationInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {

    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        assert($metadata instanceof MapperMetadata);

        $metadata->forMember('password', function (UserApi $userApi, User $user) {
            return $this->userPasswordHasher->hashPassword($user, $userApi->password);
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
