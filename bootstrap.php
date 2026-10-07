<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

/** @var $events \TightenCo\Jigsaw\Events\EventBus */

$events->afterBuild(App\Listeners\GenerateSitemap::class);
$events->afterBuild(App\Listeners\GenerateRobots::class);
