<?php

namespace RobotsTxtParser;

class CleanParamTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @link https://help.yandex.ru/webmaster/controlling-robot/robots-txt.xml#clean-param
	 *
	 * @dataProvider generateDataForTest
	 */
	public function testCleanParam($robotsTxtContent, $message = NULL)
	{
		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules();
		$this->assertArrayHasKey('*', $rules);
		$this->assertArrayHasKey('clean-param', $rules['*']);
		$this->assertEquals(array('utm_source&utm_medium&utm.campaign'), $rules['*']['clean-param'], $message);
	}

	/**
	 * @dataProvider dataCleanParamWithPathTest
	 */
	public function testCleanParamWithPath($robotsTxtContent, $expectedCleanParamValue)
	{
		$parser = new RobotsTxtParser($robotsTxtContent);
		$rules = $parser->getRules();
		$this->assertArrayHasKey('*', $rules);
		$this->assertArrayHasKey('clean-param', $rules['*']);
		$this->assertEquals(array($expectedCleanParamValue), $rules['*']['clean-param']);
	}

	/**
	 * Generate test case data
	 * @return array
	 */
	public function generateDataForTest()
	{
		return array(
			array(
				"
				User-Agent: *
				#Clean-param: utm_source_commented&comment
				Clean-param: utm_source&utm_medium&utm.campaign
				",
				'with comment'
			),
			array(
				"
				User-Agent: *
				Clean-param: utm_source&utm_medium&utm.campaign
				Clean-param: utm_source&utm_medium&utm.campaign
				",
				'expected to remove repetitions of lines'
			),
		);
	}

	/**
	 * Generate test case data
	 * @return array
	 */
	public function dataCleanParamWithPathTest()
	{
		return array(
			array("
				User-Agent: *
				Clean-param: param1 /path/file.php
				",
				"param1 /path/file.php",
			),
		);
	}
}
