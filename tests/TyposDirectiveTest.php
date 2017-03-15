<?php

class TyposDirectiveTest extends \PHPUnit\Framework\TestCase
{
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__ . '/../RobotsTxtParser.php'));
	}

	/**
	 * @dataProvider generateDataForTest
	 */
	public function testTyposDirective($robotsTxtContent, array $expectedDisallow)
	{
		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);

		$rules = $parser->getRules('*');

		$this->assertNotEmpty($rules['disallow'], 'not found disallow rules');
		$this->assertEquals($expectedDisallow, $rules['disallow']);
	}

	/**
	 * Generate test data
	 *
	 * @return array
	 */
	public function generateDataForTest()
	{
		return array(
			array("
					User-agent: *
					Disallow: /tech1
					Disalow: /tech2
					Disallow: /tech3
				",
				array(
					'/tech1',
					'/tech3',
				),
			),
		);
	}
}