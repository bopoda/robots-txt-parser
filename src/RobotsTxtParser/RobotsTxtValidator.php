<?php

namespace RobotsTxtParser;

/**
 * Class designed to check if url is allowed to be crawled by specific user-agent according to robots.txt rules.
 */
class RobotsTxtValidator
{
    /**
     * @var array  Data with ordered rules to determine isUrl Allow/Disallow
     */
    private $orderedDirectivesCache;

    /**
     * @var array All rules from RobotsTxtParser
     */
    private $rules;

    /**
     * RobotsTxtValidator constructor
     *
     * @param array $rules Array of all rules from class RobotsTxtParser
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Return true if url is allow to crawl by robots.txt rules otherwise false
     *
     * @param string $url Should be relative
     * @param string $userAgent
     * @return bool
     */
    public function isUrlAllow($url, $userAgent = '*')
    {
        $relativeUrl = $this->getRelativeUrl($url);

        $orderedDirectives = $this->getOrderedDirectivesByUserAgent($userAgent);
        // if find no directive for particular User Agent then find for all '*'
        if (count($orderedDirectives) == 0 && $userAgent != '*') {
            $orderedDirectives = $this->getOrderedDirectivesByUserAgent('*');
        }

        // if has not allow rules we can determine when url disallowed even on one coincidence - just to do it faster.
        $hasAllowDirectives = true;
        foreach ($orderedDirectives as $directiveRow) {
            if ($directiveRow['directive'] == 'allow') {
                $hasAllowDirectives = true;
                break;
            }
        }

        $isAllow = true;
        foreach ($orderedDirectives as $directiveRow) {
            if (!in_array($directiveRow['directive'], array('allow', 'disallow'))) {
                continue;
            }

            if (preg_match($directiveRow['rule_regexp'], $relativeUrl)) {
                if ($directiveRow['directive'] == 'allow') {
                    $isAllow = true;
                } else {
                    if (!$hasAllowDirectives) {
                        return false;
                    }

                    $isAllow = false;
                }
            }
        }

        return $isAllow;
    }

    /**
     * Return true if url is disallow to crawl by robots.txt rules otherwise false
     *
     * @param string $url
     * @param string $userAgent
     * @return bool
     */
    public function isUrlDisallow($url, $userAgent = '*')
    {
        return !$this->isUrlAllow($url, $userAgent);
    }

    /**
     * Get array of ordered by length rules from allow and disallow directives by specific user-agent
     * If you have already stored robots.txt rules into database, you can use query like this to fetch ordered rules:
     * mysql> SELECT directive,value FROM robots_txt where site_id = ?d and directive IN ('allow','disallow) AND user_agent = ? ORDER BY CHAR_LENGTH(value) ASC;
     *
     * @param string $userAgent
     * @return array
     */
    private function getOrderedDirectivesByUserAgent($userAgent)
    {
        if (!isset($this->orderedDirectivesCache[$userAgent])) {
            if (!empty($this->rules[$userAgent])) {
                //put data to execution cache
                $this->orderedDirectivesCache[$userAgent] = $this->orderDirectives($this->rules[$userAgent]);
            } else {
                $this->orderedDirectivesCache[$userAgent] = array();
            }
        }

        return $this->orderedDirectivesCache[$userAgent];
    }

    /**
     * Order directives by rule char length
     *
     * @param array $rules
     * @return array $directives
     */
    private function orderDirectives(array $rules)
    {
        $directives = array();

        $allowRules = !empty($rules['allow']) ? $rules['allow'] : array();
        $disallowRules = !empty($rules['disallow']) ? $rules['disallow'] : array();

        foreach ($allowRules as $rule) {
            $directives[] = array(
                'directive' => 'allow',
                'rule' => $rule,
                'rule_regexp' => $this->prepareRegexpRule($rule),
            );
        }

        foreach ($disallowRules as $rule) {
            $directives[] = array(
                'directive' => 'disallow',
                'rule' => $rule,
                'rule_regexp' => $this->prepareRegexpRule($rule),
            );
        }

        usort($directives, function ($row1, $row2) {
            return mb_strlen($row1['rule']) > mb_strlen($row2['rule']);
        });

        return $directives;
    }

    /**
     * Always returns relative url without domain which start from "/", e.g.:
     *
     * http://example.com/test       -> /test
     * https://example.com/test/path -> /test/path
     * /test/any/path                -> /test/any/path
     * http://example.com            -> /
     * /                             -> /
     * /some/path                    -> /some/path
     *
     * @param string $url
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getRelativeUrl($url)
    {
        if (!$url) {
            throw new \InvalidArgumentException('Url should not be empty');
        }

        if (!preg_match('!^https?://!i', $url)) {
            if (empty($url[0]) || $url[0] !== '/') {
                throw new \InvalidArgumentException('Url should start from "/" or has protocol with domain, got ' . $url);
            } else {
                return $url;
            }
        }

        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            throw new \InvalidArgumentException('Can\'t parse url, probably url is invalid');
        }
        if (isset($parsedUrl['host']) && !isset($parsedUrl['path'])) {
            return '/';
        }
        // make url relative
        return ((isset($parsedUrl['path']) ? "{$parsedUrl['path']}" : '') .
            (isset($parsedUrl['query']) ? "?{$parsedUrl['query']}" : ''));
    }

    /**
     * Convert robots.txt rule to php RegExp
     *
     * @param string $ruleValue
     * @return string
     */
    private static function prepareRegexpRule($ruleValue)
    {
        $replacementsBeforeQuote = [
            '*' => '_ASTERISK_WILDCARD_',
            '$' => '_DOLLAR_WILDCARD_',
        ];

        $replacementsAfterQuote = [
            '_ASTERISK_WILDCARD_' => '.*',
            '_DOLLAR_WILDCARD_' => '$',
        ];

        $regexp = str_replace(
            array_keys($replacementsBeforeQuote),
            array_values($replacementsBeforeQuote),
            $ruleValue
        );

        $regexp = preg_quote($regexp, '/');

        $regexp = str_replace(
            array_keys($replacementsAfterQuote),
            array_values($replacementsAfterQuote),
            $regexp
        );

        return '/^' . $regexp . '/';
    }
}
