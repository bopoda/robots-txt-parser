<?php

class DifferentCasesTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
	}

	/**
	 * https://github.com/bopoda/robots-txt-parser/issues/5
	 */
	public function testIssue5()
	{
		$robotsTxtContent = "
				User-agent:Googlebot
				Disallow:/
				User-agent:Cocon.Se Crawler
				Disallow:/
			";

		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);

		$rules = $parser->getRules();

		$this->assertArrayHasKey('googlebot', $rules, 'googlebot rules is empty');
		$this->assertArrayHasKey('cocon.se crawler', $rules, 'cocon.se crawler rules is empty');
	}
}
