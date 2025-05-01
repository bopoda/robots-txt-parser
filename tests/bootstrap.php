<?php

require __DIR__ . '/../vendor/autoload.php';

// backward compatibility for php 5.5 and low (with phpunit < v.6)
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}
