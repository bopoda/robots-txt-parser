<?php

class AbsolutePathTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
	}

	/**
	 * @dataProvider generateDataForTest
	 */
	public function testDifferentDisallowPath($robotsTxtContent, $expectedDisallow = NULL)
	{
		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		$rules = $parser->getRules('*');

		if (!$expectedDisallow) {
			$this->assertEmpty($rules, 'expected empty rules');
		}
		else {
			$this->assertArrayHasKey('disallow', $rules);
			$this->assertEquals(1, count($rules['disallow']));
			$this->assertEquals($expectedDisallow, $rules['disallow'][0]);
		}
	}

	/**
	 * Generate test case data
	 * @return array
	 */
	public function generateDataForTest()
	{
		return array(
			array(
				"
				User-Agent: *
				Disallow:
				",
				NULL
			),
			array(
				"
				User-Agent: *
				Disallow: /path
				",
				'/path'
			),
			array(
				"
				User-Agent: *
				Disallow: http://example.com/path
				",
				'http://example.com/path'
			),
		);
	}
}
