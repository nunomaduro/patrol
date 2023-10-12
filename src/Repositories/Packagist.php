<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Repositories;

use Composer\Semver\Semver;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use NunoMaduro\Patrol\Support\Collection;
use NunoMaduro\Patrol\Support\ComposerLock;

use function NunoMaduro\Patrol\Support\collect;

/**
 * @internal
 */
final class Packagist
{
    /**
     * The Security Advisories URL.
     */
    private const PACKAGIST_URL = 'https://packagist.org/api/security-advisories';

    /**
     * @var Collection<array>|null
     */
    private ?Collection $vulnerabilities = null;

    /**
     * Creates a new vulnerabilities repository.
     */
    public function __construct(private ClientInterface $httpClient, private ComposerLock $composerLock)
    {
        // ..
    }

    /**
     * Returns the vulnerabilities of the given dependency name.
     *
     * @return Collection<array<string, string>>
     */
    public function vulnerabilitiesOf(string $dependency): Collection
    {
        return $this->vulnerabilities()->filter(
            fn ($vulnerability) => $vulnerability['packageName'] === $dependency
        );
    }

    /**
     * Gets the vulnerabilities.
     *
     * @return Collection<array<string, string>>
     */
    private function vulnerabilities(): Collection
    {
        if ($this->vulnerabilities) {
            return $this->vulnerabilities;
        }

        return $this->vulnerabilities = collect($this->composerLock->dependencies())
            ->chunk(30)
            ->map(function (Collection $chunk) {
                try {
                    $response = $this->httpClient->request('GET', self::PACKAGIST_URL, [
                        'query' => ['packages' => $chunk->keys()->toArray()],
                    ])->getBody()->getContents();
                } catch (RequestException $e) {
                    // ..

                    return [];
                }

                return collect(json_decode($response, true)['advisories'])
                    ->values()
                    ->flatten()
                    ->map(function ($vulnerability) use ($chunk) {
                        $effectedVersions = array_map(
                            fn ($version) => str_replace(',', ' ', $version),
                            explode('|', $vulnerability['affectedVersions'])
                        );

                        $packageName = $vulnerability['packageName'];

                        foreach ($effectedVersions as $effectedVersion) {
                            if (Semver::satisfies($chunk->get($packageName), $effectedVersion)) {
                                return array_merge($vulnerability, [
                                    'affectedVersions' => $effectedVersion,
                                    'current' => $chunk->get($packageName),
                                ]);
                            }
                        }
                    })->filter()->toArray();
            })
            ->flatten();
    }
}
