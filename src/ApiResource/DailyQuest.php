<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use App\State\DailyQuestStateProvider;

#[ApiResource(
    shortName: 'Quest',
    provider: DailyQuestStateProvider::class,
)]
class DailyQuest
{

}
