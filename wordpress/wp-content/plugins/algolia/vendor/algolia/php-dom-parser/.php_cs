<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'tests')
;

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(array(
        'align_double_arrow',
    ))
    ->finder($finder)
;
