<?php

namespace RobotsTxtParser;

class HostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testHost($robotsTxtContent, $expectedHost = null, $message = '')
    {
        // init parser
        $parser = new RobotsTxtParser($robotsTxtContent);
        $rules = $parser->getRules();
        $this->assertArrayHasKey('*', $rules, $message);

        if ($expectedHost) {
            $this->assertArrayHasKey('host', $rules['*'], $message);
            $this->assertEquals($expectedHost, $rules['*']['host'], $message);
        } else {
            $this->assertEmpty(@$rules['*']['host'], $message);
        }
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
				",
                null,
            ),
            array(
                "
					User-Agent: *
					Host: www.example.com
				",
                'www.example.com',
            ),
            array(
                "
					Host: example.com
				",
                'example.com',
            ),
            array(
                "
				Host: example.com
				User-Agent: google
				Host: www.example.com
				",
                'example.com',
                'expected first host value'
            ),
            array(
                "
				User-Agent: google
				Host: example.com
				Host: www.example.com
				",
                'example.com',
                'expected assign host to * because host is cross-section directive'
            ),
        );
    }
}
