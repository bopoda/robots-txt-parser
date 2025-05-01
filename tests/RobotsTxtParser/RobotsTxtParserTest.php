<?php

namespace RobotsTxtParser;

use PHPUnit\Framework\TestCase;

class RobotsTxtParserTest extends TestCase
{
    /**
     * @dataProvider lineEndingProvider
     */
    public function testParsesRulesWithVariousLineEndings(
        string $content,
        array $expectedDisallow,
        array $expectedAllow,
        array $expectedSitemaps
    ): void {
        $parser = new RobotsTxtParser($content);

        $rules = $parser->getRules('GoogleBot');
        $sitemaps = $parser->getSitemaps();

        $this->assertSame($expectedDisallow, $rules['disallow'] ?? []);
        $this->assertSame($expectedAllow, $rules['allow'] ?? []);
        $this->assertSame($expectedSitemaps, $sitemaps);
    }

    public static function lineEndingProvider(): array
    {
        return [
            'Unix line endings (\n)' => [
                "User-agent: GoogleBot\nDisallow: /private/\nAllow: /public/\nSitemap: https://example.com/sitemap.xml\n",
                ['/private/'],
                ['/public/'],
                ['https://example.com/sitemap.xml'],
            ],
            'Windows line endings (\r\n)' => [
                "User-agent: GoogleBot\r\nDisallow: /private/\r\nAllow: /public/\r\nSitemap: https://example.com/sitemap.xml\r\n",
                ['/private/'],
                ['/public/'],
                ['https://example.com/sitemap.xml'],
            ],
            'Old Mac line endings (\r)' => [
                "User-agent: GoogleBot\rDisallow: /private/\rAllow: /public/\rSitemap: https://example.com/sitemap.xml\r",
                ['/private/'],
                ['/public/'],
                ['https://example.com/sitemap.xml'],
            ],
        ];
    }
}
