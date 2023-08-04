<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class UserApi
{
    public ?int $id = null;
}
