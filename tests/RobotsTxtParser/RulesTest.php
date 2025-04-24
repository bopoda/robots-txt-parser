<?php

namespace RobotsTxtParser;

class RulesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataForTest
     */
    public function testGetRules($robotsTxtContent, $expectedRules = array())
    {
        // init parser
        $parser = new RobotsTxtParser($robotsTxtContent);
        $this->assertEquals($expectedRules, $parser->getRules());
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
					User-Agent: *
					Disallow: /ajax
					Disallow: /search
					Clean-param: param1 /path/file.php

					User-agent: Yahoo
					Disallow: /

					Host: example.com
				",
                'expectedRules' => array(
                    '*' => array(
                        'disallow' => array(
                            0 => '/ajax',
                            1 => '/search',
                        ),
                        'clean-param' => array(
                            0 => 'param1 /path/file.php',
                        ),
                        'host' => 'example.com',
                    ),
                    'yahoo' => array(
                        'disallow' => array(
                            0 => '/',
                        ),
                    ),
                )
            ),
            array(
                "
					User-Agent: yandex
					Clean-param: param1&param2 /path/file.php

					Host: www.example.com
				",
                'expectedRules' => array(
                    '*' => array(
                        'host' => 'www.example.com',
                    ),
                    'yandex' => array(
                        'clean-param' => array(
                            0 => 'param1&param2 /path/file.php',
                        ),
                    ),
                )
            ),
            array(
                "
					User-agent: Yandex
					Allow: /archive
					Disallow: /
					# разрешает все, что содержит '/archive', остальное запрещено

					User-agent: Yandex
					Allow: /obsolete/private/*.html$ # разрешает html файлы
													 # по пути '/obsolete/private/...'
					Disallow: /*.php$  # запрещает все '*.php' на данном сайте
					Disallow: /*/private/ # запрещает все подпути содержащие
										  # '/private/', но Allow выше отменяет
										  # часть запрета
					Disallow: /*/old/*.zip$ # запрещает все '*.zip' файлы, содержащие
											# в пути '/old/'

					User-agent: Yandex
					Disallow: /add.php?*user=
					# запрещает все скрипты 'add.php?' с параметром 'user'
				",
                'expectedRules' => array(
                    'yandex' => array(
                        'allow' => array(
                            0 => '/archive',
                            1 => '/obsolete/private/*.html$',
                        ),
                        'disallow' => array(
                            0 => '/',
                            1 => '/*.php$',
                            2 => '/*/private/',
                            3 => '/*/old/*.zip$',
                            4 => '/add.php?*user=',
                        ),
                    ),
                ),
            ),
        );
    }
}
