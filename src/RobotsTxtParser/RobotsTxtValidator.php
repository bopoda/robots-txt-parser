<?php

namespace RobotsTxtParser;

/**
 * @psalm-suppress UnusedClass
 *
 * Class designed to check if URL is allowed to be crawled by specific user-agent according to robots.txt rules.
 */
class RobotsTxtValidator
{
    /**
     * @var array<string, array>  Cache of ordered rules by user-agent
     */
    private array $orderedDirectivesCache = [];

    /**
     * @var array<string, array> Rules from RobotsTxtParser
     */
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function isUrlAllow(string $url, string $userAgent = '*'): bool
    {
        $relativeUrl = $this->getRelativeUrl($url);
        $orderedDirectives = $this->getOrderedDirectivesByUserAgent($userAgent);

        // if find no directive for particular User Agent then find for all '*'
        if (empty($orderedDirectives) && $userAgent !== '*') {
            $orderedDirectives = $this->getOrderedDirectivesByUserAgent('*');
        }

        // if has not allow rules we can determine when url disallowed even on one coincidence - just to do it faster.
        $hasAllowDirectives = false;
        foreach ($orderedDirectives as $directiveRow) {
            if ($directiveRow['directive'] === 'allow') {
                $hasAllowDirectives = true;
                break;
            }
        }

        $isAllow = true;
        foreach ($orderedDirectives as $directiveRow) {
            if (!in_array($directiveRow['directive'], ['allow', 'disallow'], true)) {
                continue;
            }

            if (preg_match($directiveRow['rule_regexp'], $relativeUrl)) {
                $isAllow = $directiveRow['directive'] === 'allow';

                if (!$isAllow && !$hasAllowDirectives) {
                    return false;
                }
            }
        }

        return $isAllow;
    }

    /**
     * Return true if url is disallow to crawl by robots.txt rules otherwise false
     */
    public function isUrlDisallow(string $url, string $userAgent = '*'): bool
    {
        return !$this->isUrlAllow($url, $userAgent);
    }

    /**
     * Get array of ordered by length rules from allow and disallow directives by specific user-agent
     * If you have already stored robots.txt rules into database, you can use query like this to fetch ordered rules:
     * mysql> SELECT directive,value FROM robots_txt where site_id = ?d and directive IN ('allow','disallow) AND user_agent = ? ORDER BY CHAR_LENGTH(value) ASC;
     */
    private function getOrderedDirectivesByUserAgent(string $userAgent): array
    {
        if (!isset($this->orderedDirectivesCache[$userAgent])) {
            $rules = $this->rules[$userAgent] ?? [];
            $this->orderedDirectivesCache[$userAgent] = $this->orderDirectives($rules);
        }

        return $this->orderedDirectivesCache[$userAgent];
    }

    /**
     * Order directives by rule char length
     */
    private function orderDirectives(array $rules): array
    {
        $directives = [];

        foreach ($rules['allow'] ?? [] as $rule) {
            $directives[] = [
                'directive' => 'allow',
                'rule' => $rule,
                'rule_regexp' => self::prepareRegexpRule($rule),
            ];
        }

        foreach ($rules['disallow'] ?? [] as $rule) {
            $directives[] = [
                'directive' => 'disallow',
                'rule' => $rule,
                'rule_regexp' => self::prepareRegexpRule($rule),
            ];
        }

        usort($directives, fn ($a, $b) => strlen($a['rule']) <=> strlen($b['rule']));

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
     * @throws \InvalidArgumentException
     */
    private function getRelativeUrl(string $url): string
    {
        if ($url === '') {
            throw new \InvalidArgumentException('Url should not be empty');
        }

        if (!preg_match('!^https?://!i', $url)) {
            if ($url[0] !== '/') {
                throw new \InvalidArgumentException("Relative URL should start with '/', got: " . $url);
            }
            return $url;
        }

        $parsed = parse_url($url);
        if ($parsed === false) {
            throw new \InvalidArgumentException('Invalid URL');
        }

        // make url relative
        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';

        return $path . $query;
    }

    /**
     * Convert robots.txt rule to PHP RegExp
     */
    private static function prepareRegexpRule(string $rule): string
    {
        // Escape special characters except * and $
        $escaped = '';
        $length = strlen($rule);

        for ($i = 0; $i < $length; $i++) {
            $char = $rule[$i];

            if ($char === '*') {
                $escaped .= '.*';
            } elseif ($char === '$') {
                $escaped .= '$';
            } else {
                $escaped .= preg_quote($char, '/');
            }
        }

        return '/^' . $escaped . '/';
    }
}
