<?php

class RulesTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
	}

	/**
	 * @dataProvider generateDataForTest
	 */
	public function testGetRules($robotsTxtContent, $expectedRules)
	{
		// init parser
		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		$this->assertEquals($expectedRules, $parser->getRules());
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
					Disallow: /ajax
					Disallow: /search
					Clean-param: param1 /path/file.php

					User-agent: Yahoo
					Disallow: /

					Host: example.com
				",
				'expectedRules' => array(
					'*' => array(
						'disallow' => array(
							0 => '/ajax',
							1 => '/search',
						),
						'clean-param' => array(
							0 => 'param1 /path/file.php',
						),
					),
					'yahoo' => array(
						'disallow' => array(
							0 => '/',
						),
						'host' => 'example.com',
					),
				)
			),
			array("
					User-Agent: yandex
					Clean-param: param1&param2 /path/file.php

					Host: www.example.com
				",
				'expectedRules' => array(
					'yandex' => array(
						'clean-param' => array(
							0 => 'param1&param2 /path/file.php',
						),
						'host' => 'www.example.com',
					),
				)
			)
		);
	}
}
