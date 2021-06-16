<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Support;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class ComposerBinary
{
    /**
     * Acts as static in memory cache.
     *
     * @var array<string, array>
     */
    private static array $cache = [];

    /**
     * Creates a new composer binary.
     */
    public function __construct(private string $directory)
    {
        // ..
    }

    /**
     * Returns all installed direct and outdated dependencies.
     *
     * @return array<array<string, string>>
     */
    public function outdated(): array
    {
        $result = current($this->run(['show --outdated --latest --no-dev --format=json']));

        $result = json_decode((string) $result, true);

        return $result['installed'] ?? [];
    }

    /**
     * Returns all installed dependencies.
     *
     * @return array<array<string, string>>
     */
    public function all(): array
    {
        $result = current($this->run(['show --latest --no-dev --format=json']));

        $result = json_decode((string) $result, true);

        return $result['installed'] ?? [];
    }

    /**
     * Returns all installed root dependencies.
     *
     * @return array<array<string, string>>
     */
    public function root(): array
    {
        $result = current($this->run(['show --latest --direct --no-dev --format=json']));

        $result = json_decode((string) $result, true);

        return $result['installed'] ?? [];
    }

    /**
     * Returns the reason why the given dependencies exist.
     *
     * @param array<string,string> $dependencies
     *
     * @return array<string,string>
     */
    public function why(array $dependencies): array
    {
        $root = collect($this->root())->map(
            fn ($dependency) => $dependency['name'],
        )->toArray();

        return collect(
            $this->run(
                collect($dependencies)->map(
                    fn ($version, $name) => 'why ' . $name . ' --recursive',
                )->toArray()
            )
        )->map(fn ($output)     => explode("\n", $output))
            ->filter(fn ($line) => !empty($line))
            ->map(fn ($output)  => collect($output)->map(
                fn ($line)      => explode(' ', $line)[0]
            )->filter()->unique()->filter(
                fn ($dependency) => in_array($dependency, $root, true)
            )->toArray()
            )->toArray();
    }

    /**
     * Returns the reason why the given dependencies are outdated.
     *
     * @return array<string,string>
     */
    public function whyNot($dependencies): array
    {
        return collect(
            $this->run(
                collect($dependencies)->map(
                    fn ($version, $name) => 'why-not ' . $name . ':' . $version,
                )->toArray()
            )
        )->map(fn ($output)     => explode("\n", $output))
            ->filter(fn ($line) => !empty($line))
            ->map(fn ($output)  => collect($output)->map(
                fn ($line)      => explode(' ', $line)[0]
            )->toArray()
            )->toArray();
    }

    /**
     * Runs the given composer command and returns it's process.
     *
     * @param array<int|string, string> $commands
     *
     * @return array<int|string, string>
     */
    private function run(array $commands): array
    {
        return collect($commands)->map(function ($command) {
            if (isset(self::$cache[$command])) {
                return [self::$cache[$command], $command];
            }

            tap($process = new Process(array_filter([
                $this->isWindows() ? null : (new PhpExecutableFinder())->find(),
                (new ExecutableFinder())->find('composer'),
                ...explode(' ', $command),
            ]), (string) realpath($this->directory)))->start();

            return [$process, $command];
        })->map(
            fn ($result) => $result[0] instanceof Process
                ? (self::$cache[$result[1]] = tap($result[0], fn ($result) => $result->wait())->getOutput())
                : $result[0]
        )->toArray();
    }
    /*
    * Checks if php executable runs on windows
    */
    private function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
