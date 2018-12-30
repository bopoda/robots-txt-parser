<?php

namespace RobotsTxtParser;

class HttpTest extends \PHPUnit\Framework\TestCase
{
	public function testGoogleCom()
	{
		$robotsTxtContent = $this->getRobotsTxtContent('google.com');

		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules('*');
		$this->assertNotEmpty($rules);
		$this->assertArrayHasKey('disallow', $rules);
		$this->assertGreaterThan(100, count($rules['disallow']), 'expected more than 100 disallow rules');
		$this->assertGreaterThan(1, count($rules['sitemap']), 'expected more than 1 sitemap');
	}

	public function testRozetkaComUa()
	{
		$robotsTxtContent = $this->getRobotsTxtContent('rozetka.com.ua');

		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules('*');

		$this->assertNotEmpty($rules);
		$this->assertArrayHasKey('disallow', $rules);
		$this->assertGreaterThan(3, count($rules['disallow']), 'expected more than 3 disallow rules');
		$this->assertNotEmpty($parser->getRules('mediapartners-google'), 'expected Mediapartners-Google rules');
		if (isset($rules['host'])) {
			$this->assertRegexp('!^(https?://)rozetka\.com\.ua$!', $rules['host'], 'strange Host detected');
		}
	}

	/**
	 * @dataProvider InvalidEncodingDomains
	 */
	public function testInvalidEncodingDomains($domain)
	{
		$robotsTxtContent = $this->getRobotsTxtContent($domain);

		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules();

		$this->assertNotEmpty($rules, 'got empty rules from ' . $domain . '/robots.txt. It seems problems with encoding or robots.txt was changed');
	}

	/**
	 * @return array
	 */
	public function InvalidEncodingDomains()
	{
		return array(
			array('dcga.fr'),
			array('recoin.fr'),
		);
	}

	private function getRobotsTxtContent($domain)
	{
		$robotsTxtContent = @file_get_contents("http://$domain/robots.txt");

		if ($robotsTxtContent === false) {
			$this->markTestSkipped('robots.txt not found');
		}

		return $robotsTxtContent;
	}
}
