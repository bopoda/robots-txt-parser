<?php

class MultipleUserAgentsRulesTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtParser.php'));
	}

	public function testParseMultipleUserAgentsRules()
	{
		$robotsTxtContent = "
			User-agent: SEOkicks-Robot
			User-agent: Spiderlytics
			User-agent: ShopWiki
			User-agent: oBot/2.3.1
				Crawl-delay: 60
			User-agent: 008
			User-agent: Accoona
			User-agent: aipbot
			User-agent: aipbot*
				disallow: /
		";

		$parser = new RobotsTxtParser($robotsTxtContent);

		foreach (['SEOkicks-Robot', 'Spiderlytics', 'ShopWiki', 'oBot/2.3.1'] as $userAgent) {
			$this->assertEquals(
				[
					'crawl-delay' => 60,
				],
				$parser->getRules($userAgent),
				'failed to get correct rules for user-agent ' .$userAgent
			);
		}

		foreach (['008', 'Accoona', 'aipbot', 'aipbot*'] as $userAgent) {
			$this->assertEquals(
				[
					'disallow' => ['/'],
				],
				$parser->getRules($userAgent),
				'failed to get correct rules for user-agent ' .$userAgent
			);
		}
	}
}
