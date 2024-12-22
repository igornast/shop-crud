<?php

declare(strict_types=1);

namespace Tests\Architecture;

arch('CODE_STYLE: declare(strict_types=1); is enforced within the application')
    ->expect('App')
    ->toUseStrictTypes();

arch('CODE_STYLE: no debugging functions present in the application')
    ->expect('App')
    ->not->toUse(['var_dump', 'dump', 'dd']);
