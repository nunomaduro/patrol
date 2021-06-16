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
final class Score
{
    /**
     * Creates a new handler instance.
     */
    public function __construct(private Composer $composer)
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
                        $directory . '/composer.lock'
                    ),
                ),
            )
        );
    }

    /**
     * Invokes the handler.
     */
    public function __invoke(InspectCommand $output): void
    {
        $output->line();

        $count = $this->composer->all()->count();
        $percentage = $count > 0 ? ($this->composer->updated()->count() * 100) / $count : 0;

        $vulnerabilities = $this->composer->all()->map(
            fn ($dependency) => $dependency['vulnerabilities']
        )->flatten();

        $asString   = $this->getPercentageAsString($percentage);
        $bgColor    = $this->getBgColor($percentage, $vulnerabilities->count());

        $resume = sprintf('%s dependencies', $count);

        $this->composer->outdated()->whenNotEmpty(function ($count) use (&$resume) {
            $resume .= sprintf(', <fg=yellow>%s outdated</>', $count);
        });

        $vulnerabilities->whenNotEmpty(function ($count) use (&$resume) {
            $resume .= sprintf(', <fg=red>%s vulnerabilit%s</>', $count, $count > 1 ? 'ies' : 'y');
        });

        $resume .= '.';

        $output->paint([
            sprintf('  <bg=%s;fg=black>           </>', $bgColor),
            sprintf('  <bg=%s;fg=black;options=bold>   %s   </>  %s', $bgColor, $asString, $resume),
            sprintf('  <bg=%s;fg=black>           </>', $bgColor),
        ]);

        if ($percentage < $output->options()['min']) {
            $output->exitWith(Command::FAILURE);
        }

        $output->line();
    }

    /**
     * Returns the percentage as 5 chars string.
     */
    private static function getPercentageAsString(float $percentage): string
    {
        $percentageString = sprintf('%s%%', $percentage === 100.0
            ? '100 '
            : number_format($percentage, 1, '.', ''));

        return str_pad($percentageString, 5);
    }

    /**
     * Returns the color for the given percentage.
     */
    private function getBgColor(float $percentage, int $vulnerabilities): string
    {
        if ($vulnerabilities > 0) {
            return 'red';
        }

        if ($percentage >= 80) {
            return 'green';
        }

        if ($percentage >= 50) {
            return 'yellow';
        }

        return 'red';
    }
}
