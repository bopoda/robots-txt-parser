<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$config = (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->append([
                __FILE__,
            ])
    )
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/var/' . basename(__FILE__) . '.cache');

//(new PhpCsFixerCodingStandard())->applyTo($config, [
//    'final_public_method_for_abstract_class' => false,
//    'no_unset_on_property' => false,
//    /** @see TypeInheritance */
//    'strict_comparison' => false,
//    'logical_operators' => false,
//    'no_multiline_whitespace_around_double_arrow' => false,
//    'class_attributes_separation' => ['elements' => [
//        'trait_import' => 'only_if_meta',
//        'const' => 'only_if_meta',
//        'case' => 'only_if_meta',
//        'property' => 'one',
//        'method' => 'one',
//    ]],
//]);

return $config;
