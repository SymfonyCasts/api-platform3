<?php

namespace App\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
class AddOwnerGroupsNormalizer implements NormalizerInterface
{
    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        dump('IT WORKS!');

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}
