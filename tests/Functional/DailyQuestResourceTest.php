<?php

namespace App\Tests\Functional;

use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DailyQuestResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testPatchCanUpdateStatus()
    {
        $yesterday = new \DateTime('-1 day');
        $this->browser()
            ->patch('/api/quests/'.$yesterday->format('Y-m-d'), [
                'json' => [
                    'status' => 'completed',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json']
            ])
            ->assertStatus(200)
            ->dump()
            ->assertJsonMatches('status', 'completed')
        ;
    }
}
