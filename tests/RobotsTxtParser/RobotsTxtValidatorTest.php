<?php

namespace RobotsTxtParser;

use PHPUnit\Framework\TestCase;

class RobotsTxtValidatorTest extends TestCase
{
    /**
     * Check is url allow for user-agent *
     *
     * @dataProvider isUrlAllowRulesWithUrlsProvider
     *
     * @param array $rules Rules "as is" from RobotsTxtParser
     * @param string $url Url to check is allowed
     * @param boolean $isAllowedExpected
     */
    public function testIsUrlAllow(array $rules, $url, $isAllowedExpected)
    {
        $robotsTxtValidator = new RobotsTxtValidator($rules);
        $this->assertEquals($isAllowedExpected, $robotsTxtValidator->isUrlAllow($url));
    }

    /**
     * @dataProvider IsUrlAllowWithSpecificUserAgentProvider
     *
     * @param array $rules Rules "as is" from RobotsTxtParser
     * @param string $url Url to check is allowed
     * @param string $userAgent Useragent which used to check is Url allow
     * @param boolean $isAllowedExpected
     */
    public function testIsUrlAllowWithSpecificUserAgent(array $rules, $url, $userAgent, $isAllowedExpected)
    {
        $robotsTxtValidator = new RobotsTxtValidator($rules);
        $this->assertEquals($isAllowedExpected, $robotsTxtValidator->isUrlAllow($url, $userAgent));
    }

    /**
     * Generate test case data
     *
     * @return array
     */
    public static function isUrlAllowRulesWithUrlsProvider()
    {
        return array(
            array(
                array(),
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
                array(
                    '*' => array(),
                ),
                '/url',
                true
            ),
            array(
                array(
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
                array(
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
                array(
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
                array(
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
     * Generate test case data
     *
     * @return array
     */
    public static function IsUrlAllowWithSpecificUserAgentProvider()
    {
        return array(
            array(
                array(
                    '*' => array(
                        'disallow' => array(
                            '/fr/'
                        ),
                    ),
                ),
                '/fr/page2',
                'MyUserAgent',
                false
            ),
        );
    }

    /**
     * @dataProvider getRelativeUrlProvider
     *
     * @param $url
     * @param $expectedUrl
     * @throws \ReflectionException
     */
    public function testGetRelativeUrl($url, $expectedUrl)
    {
        $class = new \ReflectionClass('RobotsTxtParser\RobotsTxtValidator');
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
    public static function getRelativeUrlProvider()
    {
        return [
            [
                'http://example.com/test',
                '/test',
            ],
            [
                'https://example.com/test/path',
                '/test/path',
            ],
            [
                '/test/any/path',
                '/test/any/path',
            ],
            [
                'http://example.com',
                '/',
            ],
            [
                'http://example.com/',
                '/',
            ],
            [
                '/',
                '/',
            ],
            [
                '/some/path',
                '/some/path',
            ]
        ];
    }
}
