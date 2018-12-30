<?php

namespace RobotsTxtParser;

/**
 * This Test Class created to check bugs/issues which are found in similar library https://github.com/t1gor/Robots.txt-Parser-Class/issues
 */
class t1gorTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test that parsing of large robots.txt file require a little time. Less than 1s for php 5.6+
	 *
	 * https://github.com/t1gor/Robots.txt-Parser-Class/issues/62
	 */
	public function testParsingPerformanceIssue62()
	{
		$robotsContent = file_get_contents(__DIR__ . '/robots.txt/goldmansachs.com');
		$startTime = microtime(true) * 1000; //ms
		$parserRobotsTxt = new RobotsTxtParser($robotsContent);
		$rules = $parserRobotsTxt->getRules();
		$endTime = microtime(true) * 1000; //ms
		if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
			$this->assertLessThanOrEqual(1000, $endTime - $startTime, 'parsing takes a lot of time');
		}
		else {
			$this->assertLessThanOrEqual(2000, $endTime - $startTime, 'parsing takes a lot of time');
		}

		$this->assertTrue((bool) $rules, 'parsed empty rules');
	}

	/**
	 * Test isUrlAllow for disallow directives which contain $
	 *
	 * https://github.com/t1gor/Robots.txt-Parser-Class/issues/63
	 */
	public function testRuleContainingDollarIssue63()
	{
		$robotsTxtContent = "
User-agent: *
Disallow: *deny_all/$";
		$robotsTxtParser = new RobotsTxtParser($robotsTxtContent);
		$validator = new RobotsTxtValidator($robotsTxtParser->getRules());
		$this->assertFalse($validator->isUrlAllow("http://mysite.com/deny_all/"), 'Disallow: *deny_all/$');

		$robotsTxtContent = "
User-agent: *
Disallow: /deny_all/$";
		$robotsTxtParser = new RobotsTxtParser($robotsTxtContent);
		$validator = new RobotsTxtValidator($robotsTxtParser->getRules());
		$this->assertFalse($validator->isUrlAllow("http://mysite.com/deny_all/"), 'Disallow: /deny_all/$');

		$robotsTxtContent = "
User-agent: *
Disallow: deny_all/$";
		$robotsTxtParser = new RobotsTxtParser($robotsTxtContent);
		$validator = new RobotsTxtValidator($robotsTxtParser->getRules());
		$this->assertTrue($validator->isUrlAllow("http://mysite.com/deny_all/"), 'Disallow: deny_all/$');
	}

	/**
	 * Test that all rules successfully parsed
	 *
	 * https://github.com/t1gor/Robots.txt-Parser-Class/issues/79
	 */
	public function testRulesLoadedIssue79()
	{
		$robotsTxtContent = <<<ROBOTS
User-agent:Googlebot
Crawl-delay: 1.5
Disallow:/
User-agent:Cocon.Se Crawler
Disallow:/
User-agent:Yandexbot
Disallow:/
User-agent:*
Disallow:
Crawl-delay: 1
ROBOTS;

		$parserRobotsTxt = new RobotsTxtParser($robotsTxtContent);
		$rules = $parserRobotsTxt->getRules();

		$this->assertEquals(
			[
				'googlebot' => [
					'crawl-delay' => 1.5,
					'disallow' => [
						0 => '/',
					],
				],
				'cocon.se crawler' => [
					'disallow' => [
						0 => '/',
					],
				],

				'yandexbot' => [
					'disallow' => [
						0 => '/',
					],
				],
				'*' => [
					'crawl-delay' => 1.0,
				],
			],
			$rules
		);
	}
}
