<?php

namespace App\Automapper;

use App\ApiResource\UserApi;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;

class UserToUserApiMapperConfiguration implements MapperConfigurationInterface
{
    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        assert($metadata instanceof MapperMetadata);

        $metadata->forMember('dragonTreasures', fn (User $user) => new ArrayCollection($user->getPublishedDragonTreasures()->getValues()));
    }

    public function getSource(): string
    {
        return User::class;
    }

    public function getTarget(): string
    {
        return UserApi::class;
    }

}
