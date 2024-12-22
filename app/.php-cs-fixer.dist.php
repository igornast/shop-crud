<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('bin')
    ->exclude('config')
    ->exclude('public')
    ->exclude('var')
    ->exclude('vendor');
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,
    ])
    ->setFinder($finder)
    ;
