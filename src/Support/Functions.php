<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Support;

/**
 * Call the given Closure with the given value then return the value.
 *
 * @template TValue
 *
 * @param  TValue  $value
 * @return TValue
 */
function tap($value, callable $callback = null)
{
    if ($callback) {
        $callback($value);
    }

    return $value;
}

/**
 * Create a new collection with the given items.
 *
 * @template TValue
 *
 * @param  array<int|string, TValue>  $items
 * @return Collection<TValue>
 */
function collect(array $items): Collection
{
    return new Collection($items);
}
