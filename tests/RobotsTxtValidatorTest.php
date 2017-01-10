<?php

class RobotsTxtValidatorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Load library
	 */
	public static function setUpBeforeClass()
	{
		require_once(realpath(__DIR__.'/../RobotsTxtValidator.php'));
	}

	/**
	 * Check is url allow for user-agent *
	 *
	 * @dataProvider isUrlAllowRulesWithUrlsProvider
	 *
	 * @param array $rules  Rules "as is" from RobotsTxtParser
	 * @param string $url  Url to check is allowed
	 * @param boolean $isAllowedExpected
	 */
	public function testIsUrlAllow(array $rules, $url, $isAllowedExpected)
	{
		$robotsTxtValidator = new RobotsTxtValidator($rules);
		$this->assertEquals($isAllowedExpected, $robotsTxtValidator->isUrlAllow($url));
	}

	/**
	 * Generate test case data
	 *
	 * @return array
	 */
	public function isUrlAllowRulesWithUrlsProvider()
	{
		return array(
			array(
				array(
				),
				'/url',
				true
			),
			array(
				array(
					'googleBot' => array(
						'disallow' => array(
							'/url'
						),
					),
				),
				'/url',
				true
			),
			array(
				array (
					'*' => array(
					),
				),
				'/url',
				true
			),
			array(
				array (
					'*' => array(
						'disallow' => array(
							'/url/2',
						),
					),
				),
				'/url',
				true
			),
			array(
				array (
					'*' => array(
						'disallow' => array(
							'/url/2',
						),
					),
				),
				'/url/2',
				false
			),
			array(
				array (
					'*' => array(
						'allow' => array(
							'/url/test2',
						),
						'disallow' => array(
							'/url',
						),
					),
				),
				'/url',
				false
			),
			array(
				array (
					'*' => array(
						'allow' => array(
							'/url/test2',
						),
						'disallow' => array(
							'/url',
						),
					),
				),
				'/url/any',
				false
			),
			array(
				array(
					'*' => array(
						'allow' => array(
							'/url/specificPath',
						),
						'disallow' => array(
							'/',
						),
					),
				),
				'/url/specificPath',
				true
			),
		);
	}

	/**
	 * @dataProvider getRelativeUrlProvider
	 *
	 * @param string $url
	 * @param string $expectedUrl
	 */
	public function testGetRelativeUrl($url, $expectedUrl)
	{
		$class = new ReflectionClass('RobotsTxtValidator');
		$method = $class->getMethod('getRelativeUrl');
		$method->setAccessible(true);
		$validator = new RobotsTxtValidator(array());

		$relativeUrl = $method->invoke($validator, $url);
		$this->assertEquals($expectedUrl, $relativeUrl);
	}

	/**
	 * Generate test case data
	 *
	 * @return array
	 */
	public function getRelativeUrlProvider()
	{
		return array(
			array(
				'http://example.com/test', '/test',
				'https://example.com/test/path', '/test/path',
				'/test/any/path', '/test/any/path',
				'http://example.com', '/',
				'/', '/',
				'/some/path', '/some/path',
			)
		);
	}
}
