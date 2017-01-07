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

	/**
	 * https://github.com/bopoda/robots-txt-parser/issues/7
	 */
	public function testIssue7()
	{
		$robotsTxtContent = "
				Sitemap: http://example.com/sitemap.xml?year=2016
				Sitemap: http://example.com/sitemap.xml?year=2016
				Sitemap: http://example.com/sitemap.xml?year=2016
				User-agent: *
				Disallow: /admin/
				Sitemap: http://somesite.com/sitemap.xml
				User-agent: Googlebot
				Sitemap: http://internet.com/sitemap.xml
				User-agent: Yahoo
				Sitemap: http://worldwideweb.com/sitemap.xml
				Sitemap: http://example.com/sitemap.xml?year=2017
			";

		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);

		$siteMaps = $parser->getSitemaps();
		$this->assertNotEmpty($siteMaps, 'got empty sitemap list');
		$this->assertEquals(5, count($siteMaps), 'wrong unique sitemap urls count');
	}

	/**
	 * https://github.com/bopoda/robots-txt-parser/pull/8#issuecomment-270947974
	 */
	public function testSitemapsUniqueness()
	{
		$robotsTxtContent = "
			Sitemap: http://example.com/sitemap.xml?year=2017
			Sitemap: http://example.com/sitemap.xml?year=2017
			Sitemap: http://example.com/sitemap.xml?year=2017
		";

		$parser = new RobotsTxtParser($robotsTxtContent);
		$this->assertInstanceOf('RobotsTxtParser', $parser);
		// Check if the number of sitemaps is 1
		$this->assertTrue(count($parser->getSitemaps()) == 1);
	}
}
