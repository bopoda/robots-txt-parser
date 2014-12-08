<?php

class HostTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
	}

	/**
	 * @dataProvider generateDataForTest
	 */
	public function testHost($robotsTxtContent)
	{
		// init parser
		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules();
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		$this->assertObjectHasAttribute('rules', $parser);
		$this->assertArrayHasKey('*', $rules);
		$this->assertArrayHasKey('host', $rules['*']);
		$this->assertEquals('www.example.com', $rules['*']['host']);
	}

	/**
	 * Generate test case data
	 * @return array
	 */
	public function generateDataForTest()
	{
		return array(
			array("
				User-Agent: *
				Host: www.example.com
			")
		);
	}
}
