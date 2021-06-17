<?php

use NunoMaduro\Patrol\Exceptions\ComposerLockNotFound;
use NunoMaduro\Patrol\Support\ComposerLock;

test('"composer.lock" file was not found', function () {
    ComposerLock::fromFile(__DIR__.'/composer.lock');
})->throws(ComposerLockNotFound::class);
