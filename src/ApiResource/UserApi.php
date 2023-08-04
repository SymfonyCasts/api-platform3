<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    provider: 'api_platform.doctrine.orm.state.collection_provider',
)]
class UserApi
{
    public ?int $id = null;
}
