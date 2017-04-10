
<?php

class Issue48Test extends \PHPUnit\Framework\TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
		require_once(realpath(__DIR__.'/../RobotsTxtValidator.php'));
	}

	public function testIsUrlAllowWithPlus()
	{
		$robotsTxtContentIssue = "
User-agent: *
Disallow: *telecommande++*
";
		$robotsTxtParser = new RobotsTxtParser($robotsTxtContentIssue);
		$robotsTxtValidator = new RobotsTxtValidator($robotsTxtParser->getRules());

		$this->assertTrue($robotsTxtValidator->isUrlAllow('/telecommandes-box-decodeur.html'));
	}
}
