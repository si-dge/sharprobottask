<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/bin',
    ])
    ->append([__DIR__ . '/worker.php']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder);