<?php

namespace RobotsTxtParser;

class RelativePathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testRelativePath($robotsTxtContent)
    {
        $parser = new RobotsTxtParser($robotsTxtContent);
        $allRules = $parser->getRules();
        $this->assertArrayHasKey('*', $allRules);

        $robotsTxtValidator = new RobotsTxtValidator($allRules);
        $this->assertTrue($robotsTxtValidator->isUrlAllow("http://1.test.cocon.se/page2"));
        $this->assertFalse($robotsTxtValidator->isUrlAllow("http://1.test.cocon.se/?replytocom=32"));
        $this->assertFalse($robotsTxtValidator->isUrlAllow("http://1.test.cocon.se/test/?replytocom=32"));
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
	Allow: /

User-agent: *
	Disallow: /?google_comment_id=*

User-agent: *
	Disallow: /?replytocom=*

User-agent: *
	Disallow: /*/?replytocom=*
	Disallow: *?example_param[]=*
				  "
            )
        );
    }
}
