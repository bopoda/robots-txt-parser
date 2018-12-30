<?php

namespace RobotsTxtParser;

class Issue48Test extends \PHPUnit\Framework\TestCase
{
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
