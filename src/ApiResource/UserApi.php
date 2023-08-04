<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\User;

#[ApiResource(
    provider: 'api_platform.doctrine.orm.state.collection_provider',
    stateOptions: new Options(entityClass: User::class),
)]
class UserApi
{
    public ?int $id = null;
}
