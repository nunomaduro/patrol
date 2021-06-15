<?php

declare(strict_types=1);

use function NunoMaduro\Patrol\Support\collect;

$finder = PhpCsFixer\Finder::create()
    ->in(collect(['bin', 'src', 'tests'])->map(
        fn ($dir) => __DIR__ . DIRECTORY_SEPARATOR . $dir,
    )->toArray())->append([
        'bin/patrol',
        '.php-cs-fixer.dist.php',
    ]);

$rules = [
    '@Symfony'               => true,
    'phpdoc_no_empty_return' => false,
    'array_syntax'           => ['syntax' => 'short'],
    'yoda_style'             => false,
    'binary_operator_spaces' => [
        'operators' => [
            '=>' => 'align',
            '='  => 'align',
        ],
    ],
    'concat_space'            => ['spacing' => 'one'],
    'not_operator_with_space' => false,
];

$rules['increment_style'] = ['style' => 'post'];

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRules($rules)
    ->setFinder($finder);
