<?php

namespace RobotsTxtParser;

class WhitespacesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testWhitespaces($robotsTxtContent)
    {
        // init parser
        $parser = new RobotsTxtParser($robotsTxtContent);

        $rules = $parser->getRules('*');

        $this->assertNotEmpty($rules, 'expected rules for *');
        $this->assertArrayHasKey('disallow', $rules);
        $this->assertNotEmpty($rules['disallow'], 'disallow failed');
        $this->assertArrayHasKey('allow', $rules);
        $this->assertNotEmpty($rules['allow'], 'allow failed');
    }

    /**
     * Generate test case data
     * @return array
     */
    public static function generateDataForTest()
    {
        return array(
            array(
                "
				User-agent: *
				Disallow : /admin
				Allow    :   /admin/front
			"
            )
        );
    }
}
