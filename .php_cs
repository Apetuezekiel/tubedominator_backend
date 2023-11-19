<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        'single_quote' => true,
        // Add more rules as needed
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__) // Adjust the path based on your project structure
            ->name('*.php')
            ->notPath('vendor')
    );
