<?php

namespace App\DataFixtures;

use App\Factory\DragonTreasureFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DragonTreasureFactory::createMany(40);
    }
}
