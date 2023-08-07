<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\State\UserApiStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ApiResource(
    paginationItemsPerPage: 5,
    stateOptions: new Options(entityClass: User::class),
    provider: UserApiStateProvider::class
)]
#[ApiFilter(SearchFilter::class, properties: [
    'username' => 'partial',
])]
class UserApi
{
    #[ApiProperty(identifier: true)]
    public User|null $id = null;

    // will not work
    // (change line also in UserApiStateProvider)
    // public ?int $id = null;

    public ?string $email = null;

    public ?string $username = null;

    /**
     * @var Collection<int, DragonTreasure>
     */
    public Collection $dragonTreasures;

    public string $foo = '';

    public function __construct()
    {
        $this->dragonTreasures = new ArrayCollection();
    }
}
