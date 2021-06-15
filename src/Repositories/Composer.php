<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Repositories;

use function NunoMaduro\Patrol\Support\collect;
use NunoMaduro\Patrol\Support\Collection;
use NunoMaduro\Patrol\Support\ComposerBinary;

/**
 * @internal
 */
final class Composer
{
    /**
     * @var array <int, string>
     */
    private array $mayBeOutdated = [
        'psr/container',
    ];

    /**
     * Creates a new Composer repository instance.
     */
    public function __construct(private ComposerBinary $composer, private Packagist $packagist)
    {
        // ..
    }

    /**
     * Returns all installed and updated direct dependencies.
     *
     * @return Collection<array<string, string>>
     */
    public function updated(): Collection
    {
        return $this->all()->filter(function ($dependency) {
            return $this->outdated()->where('name', $dependency['name']) === null;
        });
    }

    /**
     * Returns all installed and outdated direct dependencies.
     *
     * @return Collection<array<string, string>>
     */
    public function outdated(): Collection
    {
        return $this->mutate($this->composer->outdated())
            ->filter(
                fn ($dependency) => !in_array($dependency['name'], $this->mayBeOutdated),
            )->filter(
                fn ($dependency) => $dependency['latest-status'] !== 'up-to-date',
            );
    }

    /**
     * Returns all installed direct dependencies.
     *
     * @return Collection<array<string, string>>
     */
    public function all(): Collection
    {
        return $this->mutate($this->composer->all())
            ->filter(
                fn ($dependency) => !in_array($dependency['name'], $this->mayBeOutdated),
            );
    }

    /**
     * Returns all installed direct dependencies.
     *
     * @return Collection<array<string, string>>
     */
    public function dependencies(): Collection
    {
        return $this->mutate($this->composer->all());
    }

    /**
     * @template TItem
     *
     * @param array<int, TItem> $items
     *
     * @return Collection<TItem>
     */
    private function mutate(array $items): Collection
    {
        $collection = collect($items);

        $collection = $collection->map(function ($dependency) {
            $dependency['description'] = rtrim($dependency['description'] ?? '', '.');

            return $dependency;
        });

        $collection = $collection->map(function ($dependency) {
            $dependency['vulnerabilities'] = $this->packagist->vulnerabilitiesOf($dependency['name'])->toArray();

            return $dependency;
        });

        $versions = $collection->keyBy('name')->map(fn ($dependency) => $dependency['latest'])->toArray();

        $why     = collect($this->composer->why($versions));
        $whyNots = collect($this->composer->whyNot($versions));

        $collection = $collection->map(function ($dependency) use ($why, $whyNots) {
            $dependency['why'] = $why->get($dependency['name'], []);
            $dependency['why-not'] = $whyNots->get($dependency['name'], []);

            return $dependency;
        });

        return $collection;
    }
}
