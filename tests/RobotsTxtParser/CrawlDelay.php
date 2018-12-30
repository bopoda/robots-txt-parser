<?php

namespace RobotsTxtParser;

class CrawlDelayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testCrawlDelay($robotsTxtContent)
    {
        // init parser
        $parser = new RobotsTxtParser($robotsTxtContent);
        $rules = $parser->getRules();
        $this->assertArrayHasKey('ahrefsbot', $rules);
        $this->assertArrayHasKey('crawl-delay', $rules['ahrefsbot']);
        $this->assertEquals(1.5, $rules['ahrefsbot']['crawl-delay']);
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
				User-Agent: AhrefsBot
				Crawl-Delay: 1.5
			"
            )
        );
    }
}
