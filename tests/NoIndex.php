<?php

class NoIndexTest extends \PHPUnit\Framework\TestCase
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
	public function testNoIndex($robotsTxtContent)
	{
		// init parser
		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		$rules = $parser->getRules();
		$this->assertArrayHasKey('*', $rules);
		$this->assertArrayHasKey('noindex', $rules['*']);
		$this->assertEquals(2, count($rules['*']['noindex']), 'wrong noindex directive count');		
	}

	/**
	 * Generate test case data
	 * @return array
	 */
	public function generateDataForTest()
	{
		return array(
			array("
 					User-agent: *
    					Noindex: /page-a.html
    					Noindex: /article-*
				  ")
		);
	}
}
