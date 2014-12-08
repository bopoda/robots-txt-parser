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
	public function testHost($robotsTxtContent, $expectedHost = NULL)
	{
		// init parser
		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules();
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		$this->assertObjectHasAttribute('rules', $parser);
		$this->assertArrayHasKey('*', $rules);

		if ($expectedHost) {
			$this->assertArrayHasKey('host', $rules['*']);
			$this->assertEquals($expectedHost, $rules['*']['host']);
		}
		else {
			$this->assertEmpty(@$rules['*']['host']);
		}
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
			",
			NULL,
			),
			array("
				User-Agent: *
				Host: www.example.com
			",
				'www.example.com',
			),
			array("
				Host: example.com
			",
			'example.com',
			),
		);
	}
}
