<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DailyQuestResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testPatchCanUpdateStatus()
    {
        // quests need at least some treasures to be available
        DragonTreasureFactory::createMany(5);

        $day = new \DateTime('-2 days');
        $this->browser()
            ->patch('/api/quests/'.$day->format('Y-m-d'), [
                'json' => [
                    'status' => 'completed',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json']
            ])
            ->assertStatus(200)
            ->assertJsonMatches('status', 'completed')
        ;
    }
}
