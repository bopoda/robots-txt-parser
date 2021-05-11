<?php

namespace RobotsTxtParser;

use \PHPUnit\Framework\TestCase;

class CrawlDelayTest extends TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testCrawlDelay($robotsTxtContent, $expectedCrawlDelay)
    {
        $parser = new RobotsTxtParser($robotsTxtContent);
        $rules = $parser->getRules();
        self::assertArrayHasKey('ahrefsbot', $rules);
        self::assertArrayHasKey('crawl-delay', $rules['ahrefsbot']);
        self::assertEquals($expectedCrawlDelay, $rules['ahrefsbot']['crawl-delay']);
    }

    /**
     * Generate test case data
     * @return array
     */
    public function generateDataForTest()
    {
        return [
            [
                <<<'EOTXT'
User-Agent: AhrefsBot
Crawl-Delay: 1.5
EOTXT
                ,
                1.5,
            ],
            [
                <<<'EOTXT'
User-Agent: AhrefsBot
Crawl-Delay: 0
EOTXT
                ,
                0,
            ],
        ];
    }
}
