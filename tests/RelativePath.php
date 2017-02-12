<?php

class RelativePathTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
		require_once(realpath(__DIR__.'/../RobotsTxtValidator.php'));		
	}

	/**
	 * @dataProvider generateDataForTest
	 */
	public function testRelativePath($robotsTxtContent)
	{
		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		$allRules = $parser->getRules();
		$this->assertArrayHasKey('*', $allRules );

		$robotsTxtValidator = new RobotsTxtValidator($allRules);
		$this->assertTrue($robotsTxtValidator->isUrlAllow("http://1.test.cocon.se/page2"));
		$this->assertFalse($robotsTxtValidator->isUrlAllow("http://1.test.cocon.se/?replytocom=32"));
		$this->assertFalse($robotsTxtValidator->isUrlAllow("http://1.test.cocon.se/test/?replytocom=32"));
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
	Allow: /

User-agent: *
	Disallow: /?google_comment_id=*

User-agent: *
	Disallow: /?replytocom=*

User-agent: *
	Disallow: /*/?replytocom=*
				  ")
		);		
	}
}
