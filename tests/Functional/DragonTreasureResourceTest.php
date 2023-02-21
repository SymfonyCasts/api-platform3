<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5);

        $this->browser()
            ->get('/api/treasures')
            ->dump()
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
        ;
    }
}
