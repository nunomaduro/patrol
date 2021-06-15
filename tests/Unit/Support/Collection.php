<?php

use function NunoMaduro\Patrol\Support\collect;

test('filter', function () {
    $values = collect(['foo', null])->filter()->toArray();
    expect($values)->toBe(['foo']);

    $values = collect(['foo', 'bar', null])->filter(
        fn ($value) => $value === 'bar',
    )->toArray();

    expect(array_values($values))->toBe(['bar']);
});

test('map', function () {
    $values = collect(['foo', 'bar'])->map(
        fn ($value) => $value
    )->toArray();

    expect($values)->sequence(
        fn ($item) => $item->toBe('foo'),
        fn ($item) => $item->toBe('bar'),
    );
});

test('implode', function () {
    $value = collect(['foo', 'bar'])->map(
            fn ($value) => $value
        )->implode(', ') . '.';

    expect($value)->toBe('foo, bar.');
});
