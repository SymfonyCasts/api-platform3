<?php

namespace App\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    public function __invoke(array $context = []): OpenApi
    {
        // TODO: Implement __invoke() method.
    }
}
