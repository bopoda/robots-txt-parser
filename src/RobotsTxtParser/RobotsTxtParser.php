<?php

namespace RobotsTxtParser;

/**
 * Class for parsing robots.txt files
 *
 * @author Eugene Yurkevich (bopodaa@gmail.com)
 *
 * Useful links and materials about robots.txt crawling
 * @link https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt
 * @link https://help.yandex.com/webmaster/controlling-robot/robots-txt.xml
 */

/**
 * @psalm-suppress UnusedClass
 */
class RobotsTxtParser
{
    // default encoding
    public const DEFAULT_ENCODING = 'UTF-8';

    // directives
    public const DIRECTIVE_NOINDEX = 'noindex';
    public const DIRECTIVE_ALLOW = 'allow';
    public const DIRECTIVE_DISALLOW = 'disallow';
    public const DIRECTIVE_HOST = 'host';
    public const DIRECTIVE_SITEMAP = 'sitemap';
    public const DIRECTIVE_USERAGENT = 'user-agent';
    public const DIRECTIVE_CRAWL_DELAY = 'crawl-delay';
    public const DIRECTIVE_CLEAN_PARAM = 'clean-param';

    //default user-agent
    public const USER_AGENT_ALL = '*';

    /**
     * List of supported directives
     * @var array<int, string>
     */
    private const ALLOWED_DIRECTIVES = [
        self::DIRECTIVE_NOINDEX,
        self::DIRECTIVE_ALLOW,
        self::DIRECTIVE_DISALLOW,
        self::DIRECTIVE_HOST,
        self::DIRECTIVE_SITEMAP,
        self::DIRECTIVE_USERAGENT,
        self::DIRECTIVE_CRAWL_DELAY,
        self::DIRECTIVE_CLEAN_PARAM,
    ];

    /**
     * @var string $content Original robots.txt content
     */
    private $content = '';

    /**
     * @var array $rules Rules with all parsed directives by all user-agents
     */
    private array $rules = [];

    /**
     * Current directive
     */
    private string $currentDirective = '';

    /**
     * @var array<string> $userAgentsBuffer Buffer of current user-agents
     */
    private array $userAgentsBuffer = [];

    /**
     * @param string $content Robots.txt content
     * @param string $encoding Encoding
     */
    public function __construct(string $content, string $encoding = self::DEFAULT_ENCODING)
    {
        $this->content = $this->prepareContent($content, $encoding);

        $this->prepareRules();
    }

    /**
     * Get rules by specific bot (user-agent)
     * Use $userAgent = NULL to get all rules for all user-agents grouped by user-agent. User-agents will return in lower case.
     * Use $userAgent = '*' to get common rules.
     * Use $userAgent = 'YandexBot' to get rules for user-agent 'YandexBot'.
     */
    public function getRules(?string $userAgent = null): array
    {
        if (is_null($userAgent)) {
            //return all rules
            return $this->rules;
        } else {
            $userAgent = mb_strtolower($userAgent);
            if (isset($this->rules[$userAgent])) {
                return $this->rules[$userAgent];
            } else {
                return [];
            }
        }
    }

    /**
     * Get sitemaps links.
     * Sitemap always relates to all user-agents and return in rules with user-agent "*"
     */
    public function getSitemaps(): array
    {
        $rules = $this->getRules(self::USER_AGENT_ALL);
        if (!empty($rules[self::DIRECTIVE_SITEMAP])) {
            return $rules[self::DIRECTIVE_SITEMAP];
        }

        return [];
    }

    /**
     * Return original robots.txt content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Parse rules
     */
    private function prepareRules(): void
    {
        $rows = explode(PHP_EOL, $this->content);

        foreach ($rows as $row) {
            $row = preg_replace('/#.*/', '', $row);
            $parts = explode(':', $row, 2);
            if (count($parts) < 2) {
                continue;
            }

            $directive = trim(strtolower($parts[0]));
            $value = trim($parts[1]);

            if (!in_array($directive, self::ALLOWED_DIRECTIVES, true)) {
                continue;
            }

            $this->handleDirective($directive, $value);
        }

        $this->removeDuplicates();
    }

    /**
     * Remove duplicates rules
     */
    private function removeDuplicates(): void
    {
        foreach (array_keys($this->rules) as $userAgent) {
            foreach (array_keys($this->rules[$userAgent]) as $directive) {
                if (is_array($this->rules[$userAgent][$directive])) {
                    $this->rules[$userAgent][$directive] = array_values(array_unique($this->rules[$userAgent][$directive]));
                }
            }
        }
    }

    /**
     * Handle directive with value
     * Assign value to directive
     */
    private function handleDirective(string $directive, string $value): void
    {
        $previousDirective = $this->currentDirective;
        $this->currentDirective = $directive;

        if ($value === "") {
            return;
        }

        switch ($directive) {
            case self::DIRECTIVE_USERAGENT:
                if ($previousDirective !== self::DIRECTIVE_USERAGENT) {
                    $this->userAgentsBuffer = [];
                }

                $userAgent = strtolower($value);
                $this->userAgentsBuffer[] = $userAgent;

                if (!isset($this->rules[$userAgent])) {
                    $this->rules[$userAgent] = [];
                }

                break;

            case self::DIRECTIVE_DISALLOW:
                foreach ($this->userAgentsBuffer as $userAgent) {
                    $this->rules[$userAgent][self::DIRECTIVE_DISALLOW][] = $value;
                }

                break;

            case self::DIRECTIVE_CRAWL_DELAY:

                foreach ($this->userAgentsBuffer as $userAgent) {
                    $this->rules[$userAgent][self::DIRECTIVE_CRAWL_DELAY] = (float)$value;
                }

                break;

            case self::DIRECTIVE_SITEMAP:
                $this->rules[self::USER_AGENT_ALL][self::DIRECTIVE_SITEMAP][] = $value;

                break;

            case self::DIRECTIVE_HOST:
                if (empty($this->rules[self::USER_AGENT_ALL][self::DIRECTIVE_HOST])) {
                    $this->rules[self::USER_AGENT_ALL][self::DIRECTIVE_HOST] = $value;
                }

                break;

            default:
                foreach ($this->userAgentsBuffer as $userAgent) {
                    $this->rules[$userAgent][$this->currentDirective][] = $value;
                }

                break;
        }
    }

    private function prepareContent(string $content, string $encoding): string
    {
        $prev = ini_get('mbstring.substitute_character');

        try {
            ini_set('mbstring.substitute_character', "none");

            $encoding = !empty($encoding) ? $encoding : mb_detect_encoding($content, mb_detect_order(), false);
            $result = mb_convert_encoding($content, 'UTF-8', $encoding);

            return $result !== false ? $result : $content;
        } finally {
            ini_set('mbstring.substitute_character', $prev);
        }
    }
}
