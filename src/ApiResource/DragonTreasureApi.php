<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\DragonTreasure;
use App\State\DtoToEntityStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource(
    shortName: 'Treasure',
    provider: EntityToDtoStateProvider::class,
    processor: DtoToEntityStateProcessor::class,
    stateOptions: new Options(entityClass: DragonTreasure::class),
)]
class DragonTreasureApi
{
    #[ApiProperty(readable: false, writable: false, identifier: true)]
    public int $id;

    public ?string $name = null;
}
