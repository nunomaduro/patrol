<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Exceptions;

/**
 * @internal
 */
final class ComposerLockNotFound extends \Exception
{
    /**
     * Creates a new Composer Lock not found exception.
     */
    public static function exception(): self
    {
        return new self('Patrol was unable to find an "composer.lock" file in your project. Did you run "composer install"?');
    }
}
