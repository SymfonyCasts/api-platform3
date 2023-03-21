<?php

namespace App\ApiPlatform;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        // TODO: Implement createFromRequest() method.
    }
}
