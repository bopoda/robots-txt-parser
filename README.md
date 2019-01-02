robots-txt-parser
=================

[![Build Status](https://travis-ci.org/bopoda/robots-txt-parser.svg?branch=master)](https://travis-ci.org/bopoda/robots-txt-parser)

## [RobotsTxtParser](https://github.com/bopoda/robots-txt-parser/blob/master/src/RobotsTxtParser/RobotsTxtParser.php) — PHP class for parsing all the directives of the robots.txt files

## [RobotsTxtValidator](https://github.com/bopoda/robots-txt-parser/blob/master/src/RobotsTxtParser/RobotsTxtValidator.php) — PHP class for check is url allow or disallow according to robots.txt rules. 

Try [demo](http://robots.jeka.by/) of RobotsTxtParser on-line on live domains.

Parsing is carried out according to the rules in accordance with Google & Yandex specifications:
* [Google: Robots.txt Specifications](https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt)
* [Yandex: Robots.txt Specifications](https://help.yandex.com/webmaster/controlling-robot/robots-txt.xml)

### Last improvements:
<ol>
<li>Pars the Clean-param directive according to the clean-param syntax.</li>
<li>Deleting comments (everything following the '#' character, up to the first line break, is disregarded)</li>
<li>The improvement of the Parse of Host — the intersection directive, should refer to the user-agent '*'; If there are multiple hosts, the search engines take the value of the first.</li>
<li>From the class, unused methods are removed, refactoring done, the scope of properties of the class is corrected.</li>
<li>Added more test cases, as well as test cases added to the whole new functionality.</li>
<li><a href="https://github.com/bopoda/robots-txt-parser/blob/master/src/RobotsTxtParser/RobotsTxtValidator.php">RobotsTxtValidator</a> class added to check if url is allowed to parsing.</li>
<li>With version 2.0, the speed of RobotsTxtParser was significantly improved.</li>
</ol>


### Supported Directives:
* DIRECTIVE_ALLOW = 'allow';
* DIRECTIVE_DISALLOW = 'disallow';
* DIRECTIVE_HOST = 'host';
* DIRECTIVE_SITEMAP = 'sitemap';
* DIRECTIVE_USERAGENT = 'user-agent';
* DIRECTIVE_CRAWL_DELAY = 'crawl-delay';
* DIRECTIVE_CLEAN_PARAM = 'clean-param';
* DIRECTIVE_NOINDEX = 'noindex';

## Installation

Install the latest version with

```bash
$ composer require bopoda/robots-txt-parser
```

## Run tests

Run phpunit tests using command

```bash
$ php vendor/bin/phpunit
```

### Usage example

You can start the parser by getting the content of a robots.txt file from a website:
```php
$parser = new RobotsTxtParser(file_get_contents('http://example.com/robots.txt'));
var_dump($parser->getRules());
```
Or simply using the contents of the file as input (ie: when the content is already cached):
```php
$parser = new RobotsTxtParser("
	User-Agent: *
	Disallow: /ajax
	Disallow: /search
	Clean-param: param1 /path/file.php

	User-agent: Yahoo
	Disallow: /

	Host: example.com
	Host: example2.com
");
var_dump($parser->getRules());
```
This will output:
```php
array(2) {
  ["*"]=>
  array(3) {
    ["disallow"]=>
    array(2) {
      [0]=>
      string(5) "/ajax"
      [1]=>
      string(7) "/search"
    }
    ["clean-param"]=>
    array(1) {
      [0]=>
      string(21) "param1 /path/file.php"
    }
    ["host"]=>
    string(11) "example.com"
  }
  ["yahoo"]=>
  array(1) {
    ["disallow"]=>
    array(1) {
      [0]=>
      string(1) "/"
    }
  }
}
```

In order to validate URL, use the RobotsTxtValidator class:
```php
$parser = new RobotsTxtParser(file_get_contents('http://example.com/robots.txt'));
$validator = new RobotsTxtValidator($parser->getRules());

$url = '/';
$userAgent = 'MyAwesomeBot';

if ($validator->isUrlAllow($url, $userAgent)) {
    // Crawl the site URL and do nice stuff
}
```

### Contribution
Feel free to create PR in this repository. Please, follow PSR style.

See the list of [contributors](https://github.com/bopoda/robots-txt-parser/graphs/contributors) which participated in this project.

### Final Notes:
Please use [v2.0](https://github.com/bopoda/robots-txt-parser/releases/tag/2.0)+ version
which works by same rules but is more highly performance.
