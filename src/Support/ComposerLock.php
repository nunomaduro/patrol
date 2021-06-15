<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Support;

use JsonException;
use NunoMaduro\Patrol\Exceptions\ComposerLockNotFound;

/**
 * @internal
 */
final class ComposerLock
{
    /**
     * Creates a new Composer Lock value object.
     *
     * @param array<string, string> $dependencies
     */
    private function __construct(private array $dependencies)
    {
    }

    /**
     * Returns the list of the composer dependencies.
     *
     * @return array<string, string>
     */
    public function dependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Creates a Composer Lock object from the given file.
     *
     * @throws ComposerLockNotFound
     */
    public static function fromFile(string $file): self
    {
        if (!file_exists($file)) {
            throw ComposerLockNotFound::exception();
        }

        try {
            $composer = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $_) {
            $composer = ['packages' => []];
        }

        return new self(array_combine(
            array_map(fn (array $detail): string => $detail['name'], $composer['packages']),
            array_map(fn (array $detail): string => $detail['version'], $composer['packages'])
        ) ?: []);
    }
}
