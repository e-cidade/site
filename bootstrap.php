<?php

declare(strict_types=1);

/** @var $events \TightenCo\Jigsaw\Events\EventBus */

$events->afterBuild(App\Listeners\GenerateSitemap::class);
