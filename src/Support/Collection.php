<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Support;

/**
 * @internal
 *
 * @template TItem
 */
class Collection
{
    /**
     * @param array<int|string, TItem> $items
     */
    public function __construct(private array $items)
    {
        // ..
    }

    /**
     * Counts the items.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Chunk the collection into chunks of the given size.
     *
     * @return self<self<TItem>>
     */
    public function chunk(int $size): self
    {
        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new self($chunk);
        }

        return new self($chunks);
    }

    /**
     * Die and var dumps the current items.
     *
     * @return never-return
     */
    public function dd()
    {
        return dd($this->items);
    }

    /**
     * Filters the items by the given callable.
     *
     * @return self<TItem>
     */
    public function filter(callable $callable = null): self
    {
        return new self(array_filter($this->items, $callable));
    }

    /**
     * Get the keys of the collection items.
     *
     * @return self<int|string>
     */
    public function keys(): self
    {
        return new self(array_keys($this->items));
    }

    /**
     * Key an associative array by a field.
     *
     * @return self<TItem>
     */
    public function keyBy(int | string $keyBy)
    {
        $results = [];

        foreach ($this->items as $item) {
            $resolvedKey = $item[$keyBy];

            $results[$resolvedKey] = $item;
        }

        return new self($results);
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @return self<TItem>
     */
    public function flatten(): self
    {
        return new self(array_merge(...$this->items));
    }

    /**
     * Returns the item at a given key.
     *
     * @param TItem|null $default
     *
     * @return TItem|null
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * Gets an item by the given where condition.
     *
     * @param TItem|null $default
     *
     * @return TItem|null
     */
    public function where(int | string $key, mixed $value, mixed $default = null): mixed
    {
        foreach ($this->items as $item) {
            if ($item[$key] === $value) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * Execute a callable over each item.
     *
     * @return self<TItem>
     */
    public function each(callable $callable): self
    {
        foreach ($this->items as $key => $item) {
            $callable($item, $key);
        }

        return $this;
    }

    /**
     * Implode the items by the given separator.
     */
    public function implode(string $separator): string
    {
        return implode($separator, $this->items);
    }

    /**
     * Run a map over each of the items.
     *
     * @template TResult
     *
     * @param callable(TItem, int|string): TResult $callable
     *
     * @return self<TResult>
     */
    public function map(callable $callable): self
    {
        $keys = array_keys($this->items);

        $items = array_map($callable, $this->items, $keys);

        return new self(array_combine($keys, $items));
    }

    /**
     * Apply the callable if the collection is not empty.
     *
     * @return self<TItem>
     */
    public function whenNotEmpty(callable $callable): self
    {
        if (($count = $this->count()) > 0) {
            $callable($count);
        }

        return $this;
    }

    /**
     * Converts the collection to the array form.
     *
     * @return array<int, TItem>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Makes a collection with only values.
     *
     * @return self<TItem>
     */
    public function values(): self
    {
        return new self(array_values($this->items));
    }

    /**
     * Makes all elements unique.
     *
     * @return self<TItem>
     */
    public function unique(): self
    {
        return new self(array_unique($this->items));
    }
}
