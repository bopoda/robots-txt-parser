robots-txt-parser
=================

php class for parse all directives from robots.txt files

[![Build Status](https://travis-ci.org/bopoda/robots-txt-parser.svg?branch=master)](https://travis-ci.org/bopoda/robots-txt-parser)

Php class to parse robots.txt rules according to Google & Yandex specifications. Specifications:
* [Google: Robots.txt Specifications](https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt)
* [Yandex: Robots.txt Specifications](https://help.yandex.com/webmaster/controlling-robot/robots-txt.xml)

It is fork of repo https://github.com/t1gor/Robots.txt-Parser-Class but some improvements here:
<ol>
<li>add Clean-param directive parse</li>
<li>remove comments</li>
<li>remove unused methods and improve class methods</li>
<li>add more tests cases</li>
</ol>


### Allowed directives:
* DIRECTIVE_ALLOW = 'allow';
* DIRECTIVE_DISALLOW = 'disallow';
* DIRECTIVE_HOST = 'host';
* DIRECTIVE_SITEMAP = 'sitemap';
* DIRECTIVE_USERAGENT = 'user-agent';
* DIRECTIVE_CRAWL_DELAY = 'crawl-delay';
* DIRECTIVE_CLEAN_PARAM = 'clean-param';

### Usage example
```php
$parser = new RobotsTxtParser(file_get_contents('http://example.com/robots.txt'));
var_dump($parser->getRules());
```