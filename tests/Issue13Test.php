
<?php

class Issue13Test extends \PHPUnit\Framework\TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
		require_once(realpath(__DIR__.'/../RobotsTxtValidator.php'));
	}

	public function testIsUrlAllow1()
	{
		$robotsTxtContentIssue = "
User-agent: *
Allow: /anyfolder   #length 10 exactly
Disallow: /*.html    #length 2 or 7?
";

		$parserRobotsTxt = new RobotsTxtParser($robotsTxtContentIssue);
		$rulesRobotsTxt = $parserRobotsTxt->getRules();
		$robotsTxtValidator = new RobotsTxtValidator($rulesRobotsTxt);

		#length 10 is more significant
		$this->assertTrue($robotsTxtValidator->isUrlAllow('/anyfolder.html'));
	}

	public function testIsUrlAllow2()
	{
		$robotsTxtContentIssue = "
User-agent: *
Allow: /any   #length 4 exactly
Disallow: /*.html    #length 2 or 7?
";
		$parserRobotsTxt = new RobotsTxtParser($robotsTxtContentIssue);
		$rulesRobotsTxt = $parserRobotsTxt->getRules();
		$robotsTxtValidator = new RobotsTxtValidator($rulesRobotsTxt);

		# it is not allowed according to google, so here length 7.
		$this->assertFalse($robotsTxtValidator->isUrlAllow('/anyfolder.html'));
	}

	public function testIsUrlAllow3()
	{
		$robotsTxtContentIssue = "
User-agent: *
Allow: /any     #length 4 
Disallow: /any*   #length 5
";
		$parserRobotsTxt = new RobotsTxtParser($robotsTxtContentIssue);
		$rulesRobotsTxt = $parserRobotsTxt->getRules();
		$robotsTxtValidator = new RobotsTxtValidator($rulesRobotsTxt);

		#disallowed because disallow length is longer (5 vs 4)
		$this->assertFalse($robotsTxtValidator->isUrlAllow('/any'));
	}
}
