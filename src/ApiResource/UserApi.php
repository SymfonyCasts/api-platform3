<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    shortName: 'User',
)]
class UserApi
{
    public ?int $id = null;
}
