<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ApiResource(
    stateOptions: new Options(entityClass: User::class),
)]
class UserApi
{
    public ?int $id = null;

    public ?string $email = null;

    public ?string $username = null;

    public Collection $dragonTreasures;

    public function __construct()
    {
        $this->dragonTreasures = new ArrayCollection();
    }
}
