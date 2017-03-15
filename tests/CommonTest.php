<?php

class CommonTest extends \PHPUnit\Framework\TestCase
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
	 * @dataProvider isUrlAllowProvider
	 *
	 * @param string $robotsTxtContent
	 * @param string $url
	 */
	public function testIsUrlAllow($robotsTxtContent, $url)
	{
		$robotsTxtParser = new RobotsTxtParser($robotsTxtContent);
		$allRules = $robotsTxtParser->getRules();

		$robotsTxtValidator = new RobotsTxtValidator($allRules);
		$this->assertTrue($robotsTxtValidator->isUrlAllow($url));
	}

	/**
	 * @dataProvider isUrlDisallowProvider
	 *
	 * @param string $robotsTxtContent
	 * @param string $url
	 */
	public function testIsUrlDisallow($robotsTxtContent, $url)
	{
		$robotsTxtParser = new RobotsTxtParser($robotsTxtContent);
		$allRules = $robotsTxtParser->getRules();

		$robotsTxtValidator = new RobotsTxtValidator($allRules);
		$this->assertFalse($robotsTxtValidator->isUrlAllow($url));
	}

	/**
	 * Generate test data to check isUrlAllow
	 *
	 * @return array
	 */
	public function isUrlAllowProvider()
	{
		$robotsFolders = glob(__DIR__ . '/data/robots*');

		$dataProvider = array();

		foreach ($robotsFolders as $robotsFolder) {
			$dataProvider = array_merge($dataProvider, $this->getDataForTestCase($robotsFolder, true));
		}

		return $dataProvider;
	}

	/**
	 * Generate test data to check isUrlDisallow
	 *
	 * @return array
	 */
	public function isUrlDisallowProvider()
	{
		$robotsFolders = glob(__DIR__ . '/data/robots*');

		$dataProvider = array();

		foreach ($robotsFolders as $robotsFolder) {
			$dataProvider = array_merge($dataProvider, $this->getDataForTestCase($robotsFolder, false));
		}

		return $dataProvider;
	}

	private function getDataForTestCase($robotsFolder, $isAllowTest)
	{
		$robotsTxtContent = file_get_contents($robotsFolder . '/robots.txt');

		$file = $robotsFolder . ($isAllowTest ? '/expectedAllow' : '/expectedDisallow');
		$urlsToCheck = array_values(array_filter(explode(PHP_EOL, file_get_contents($file))));
		if (empty($urlsToCheck)) {
			$this->markTestSkipped('not found test for allow urls in file ' . $file);
		}

		$dataProvider = array();

		foreach ($urlsToCheck as $url) {
			$dataProvider[] = array(
				$robotsTxtContent,
				$url
			);
		}

		return $dataProvider;
	}
}
