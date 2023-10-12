<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Handlers;

use GuzzleHttp\Client;
use NunoMaduro\Patrol\Commands\InspectCommand;
use NunoMaduro\Patrol\Repositories\Composer;
use NunoMaduro\Patrol\Repositories\Packagist;
use NunoMaduro\Patrol\Support\ComposerBinary;
use NunoMaduro\Patrol\Support\ComposerLock;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
final class DependenciesList
{
    /**
     * Creates a new handler instance.
     */
    public function __construct(private Composer $composer, private Packagist $packagist)
    {
        // ..
    }

    /**
     * Resolves the handler.
     */
    public static function resolve(string $directory): self
    {
        return new self(
            new Composer(
                new ComposerBinary($directory),
                new Packagist(
                    new Client(),
                    ComposerLock::fromFile(
                        $directory.'/composer.lock'
                    ),
                ),
            ),
            new Packagist(
                new Client(),
                ComposerLock::fromFile(
                    $directory.'/composer.lock'
                ),
            ),
        );
    }

    /**
     * Invokes the handler.
     */
    public function __invoke(InspectCommand $output): void
    {
        $this->composer
            ->all()
            ->map(function ($dependency) use ($output) {
                if (! empty($dependency['vulnerabilities'])) {
                    $output->exitWith(Command::FAILURE);
                }

                return $dependency;
            })->filter(
                fn ($dependency) => $dependency['latest-status'] !== 'up-to-date' || ! empty($dependency['vulnerabilities'])
            )->each(fn ($dependency) => $output->outdatedInfo($dependency));
    }
}
