<?php

namespace RobotsTxtParser;

class TyposDirectiveTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @dataProvider generateDataForTest
	 */
	public function testTyposDirective($robotsTxtContent, array $expectedDisallow)
	{
		$parser = new RobotsTxtParser($robotsTxtContent);

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