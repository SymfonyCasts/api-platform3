<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    shortName: 'User',
    provider: CollectionProvider::class,
)]
class UserApi
{
    public ?int $id = null;
}
