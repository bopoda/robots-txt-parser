<?php

class EmptyDisallowTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
	}

	public function testEmptyDisallow()
	{
		$robotsTxtContent = <<<'EOTXT'
User-Agent: *
Disallow:
Disallow: /foo
Disallow: /bar
EOTXT;

		$parser = new RobotsTxtParser($robotsTxtContent);

		$rules = $parser->getRules('*');
		$this->assertNotEmpty($rules);
		$this->assertArrayHasKey('disallow', $rules);
		$this->assertCount(2, $rules['disallow']);
	}

	public function testEmptyDisallowWithDoubleSections()
	{
		$robotsTxtContent = <<<'EOTXT'
User-agent: *
Disallow:

User-agent: Linguee
Disallow: /

User-agent: *
Disallow: /api/showcase
EOTXT;

		$parser = new RobotsTxtParser($robotsTxtContent);

		$rules = $parser->getRules('*');
		$this->assertNotEmpty($rules);
		$this->assertArrayHasKey('disallow', $rules);

		$this->assertCount(1, $rules['disallow']);
	}}
