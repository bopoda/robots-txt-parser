<?php

namespace RobotsTxtParser;

class CommentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testRemoveComments($robotsTxtContent)
    {
        $parser = new RobotsTxtParser($robotsTxtContent);
        $rules = $parser->getRules('*');

        $this->assertEmpty($rules, 'expected remove comments');
    }

    /**
     * @dataProvider generateDataFor2Test
     */
    public function testRemoveCommentsFromValue($robotsTxtContent, $expectedDisallowValue)
    {
        $parser = new RobotsTxtParser($robotsTxtContent);


        $rules = $parser->getRules('*');

        $this->assertNotEmpty($rules, 'expected data');
        $this->assertArrayHasKey('disallow', $rules);
        $this->assertNotEmpty($rules['disallow'], 'disallow expected');
        $this->assertEquals($expectedDisallowValue, $rules['disallow'][0]);
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
				User-agent: *
				#Disallow: /tech
			"
            ),
            array(
                "
				User-agent: *
				Disallow: #/tech
			"
            ),
            array(
                "
				User-agent: *
				Disal # low: /tech
			"
            ),
            array(
                "
				User-agent: *
				Disallow#: /tech # ds
			"
            ),
        );
    }

    /**
     * Generate test case data
     * @return array
     */
    public function generateDataFor2Test()
    {
        return array(
            array(
                "User-agent: *
					Disallow: /tech #comment",
                'disallowValue' => '/tech',
            ),
        );
    }
}
