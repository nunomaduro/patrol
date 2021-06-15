<?php

use function NunoMaduro\Patrol\Support\tap;

it('runs the given closure with the given object', function () {
    $object = new stdClass();
    $object->property = 1;

    $return = tap($object, fn ($object) => $object->property++);

    expect($return)->toBe($object)->and($object->property)->toBe(2);
    expect(tap($object))->toBe($object);
});
