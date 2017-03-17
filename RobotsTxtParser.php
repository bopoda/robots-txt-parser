<?php

/**
 * Class for parsing robots.txt files
 *
 * @author Eugene Yurkevich (bopodaa@gmail.com)
 *
 * Useful links and materials about robots.txt crawling
 * @link https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt
 * @link https://help.yandex.com/webmaster/controlling-robot/robots-txt.xml
 */
class RobotsTxtParser
{
	// default encoding
	const DEFAULT_ENCODING = 'UTF-8';

	// directives
	const DIRECTIVE_NOINDEX = 'noindex';
	const DIRECTIVE_ALLOW = 'allow';
	const DIRECTIVE_DISALLOW = 'disallow';
	const DIRECTIVE_HOST = 'host';
	const DIRECTIVE_SITEMAP = 'sitemap';
	const DIRECTIVE_USERAGENT = 'user-agent';
	const DIRECTIVE_CRAWL_DELAY = 'crawl-delay';
	const DIRECTIVE_CLEAN_PARAM = 'clean-param';

	//default user-agent
	const USER_AGENT_ALL = '*';

	/**
	 * @var string $content  Original robots.txt content
	 */
	private $content = '';

	/**
	 * @var array $rules  Rules with all parsed directives by all user-agents
	 */
	private $rules = [];

	/**
	 * @var string $currentDirective  Current directive
	 */
	private $currentDirective;

	/**
	 * @var string $userAgent  Current user-agent
	 */
	private $userAgent;

	/**
	 * @param string $content  Robots.txt content
	 * @param string $encoding  Encoding
	 * @return RobotsTxtParser
	 */
	public function __construct($content, $encoding = self::DEFAULT_ENCODING)
	{
		// convert encoding
		$encoding = !empty($encoding) ? $encoding : mb_detect_encoding($content, mb_detect_order(), false);
		if ($encoding == "UTF-8") {
			$content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
		}
		// set content
		$this->content = iconv(mb_detect_encoding($content, mb_detect_order(), false), "UTF-8//IGNORE", $content);

		$this->prepareRules();
	}

	/**
	 * Get rules by specific bot (user-agent)
	 * Use $userAgent = NULL to get all rules for all user-agents grouped by user-agent. User-agents will return in lower case.
	 * Use $userAgent = '*' to get common rules.
	 * Use $userAgent = 'YandexBot' to get rules for user-agent 'YandexBot'.
	 *
	 * @param string $userAgent
	 * @return array
	 */
	public function getRules($userAgent = NULL)
	{
		if (is_null($userAgent)) {
			//return all rules
			return $this->rules;
		}
		else {
			$userAgent = mb_strtolower($userAgent);
			if (isset($this->rules[$userAgent])) {
				return $this->rules[$userAgent];
			}
			else {
				return [];
			}
		}
	}

	/**
	 * Get sitemaps links.
	 * Sitemap always relates to all user-agents and return in rules with user-agent "*"
	 *
	 * @return array
	 */
	public function getSitemaps()
	{
		$rules = $this->getRules(self::USER_AGENT_ALL);
		if (!empty($rules[self::DIRECTIVE_SITEMAP])) {
			return $rules[self::DIRECTIVE_SITEMAP];
		}

		return [];
	}

	/**
	 * Return original robots.txt content
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Return array of supported directives
	 *
	 * @return array
	 */
	private function getAllowedDirectives()
	{
		return [
			self::DIRECTIVE_NOINDEX,
			self::DIRECTIVE_ALLOW,
			self::DIRECTIVE_DISALLOW,
			self::DIRECTIVE_HOST,
			self::DIRECTIVE_SITEMAP,
			self::DIRECTIVE_USERAGENT,
			self::DIRECTIVE_CRAWL_DELAY,
			self::DIRECTIVE_CLEAN_PARAM,
		];
	}

	/**
	 * Parse rules
	 *
	 * @return void
	 */
	private function prepareRules()
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

			$this->handleDirective($directive, $value);
		}

		$this->removeDublicates();
	}

	private function removeDublicates()
	{
		foreach ($this->rules as $userAgent => $rules) {
			foreach ($this->rules[$userAgent] as $directive => $value) {
				if (is_array($this->rules[$userAgent][$directive])) {
					$this->rules[$userAgent][$directive] = array_values(array_unique($this->rules[$userAgent][$directive]));
				}
			}
		}
	}

	/**
	 * Handle directive with value
	 * Assign value to directive
	 *
	 * @param string $directive
	 * @param string $value
	 */
	private function handleDirective($directive, $value)
	{
		if (!in_array($directive, $this->getAllowedDirectives())) {
			return;
		}

		switch ($directive) {
			case self::DIRECTIVE_USERAGENT:
				$this->currentDirective = $directive;
				$this->userAgent = strtolower($value);

				if (!isset($this->rules[$this->userAgent])) {
					$this->rules[$this->userAgent] = [];
				}

				break;

			case self::DIRECTIVE_DISALLOW:
				$this->currentDirective = $directive;

				if ($this->userAgent && $value) {
					$this->rules[$this->userAgent][self::DIRECTIVE_DISALLOW][] = $value;
				}

				break;
			case self::DIRECTIVE_CRAWL_DELAY:
				$this->currentDirective = $directive;

				if ($this->userAgent && $value) {
					$this->rules[$this->userAgent][self::DIRECTIVE_CRAWL_DELAY] = (double) $value;
				}

				break;

			case self::DIRECTIVE_SITEMAP:
				$this->currentDirective = $directive;

				if ($value) {
					$this->rules[self::USER_AGENT_ALL][self::DIRECTIVE_SITEMAP][] = $value;
				}

				break;

			case self::DIRECTIVE_HOST:
				$this->currentDirective = $directive;

				if ($value && empty($this->rules[self::USER_AGENT_ALL][self::DIRECTIVE_HOST])) {
					$this->rules[self::USER_AGENT_ALL][self::DIRECTIVE_HOST] = $value;
				}

				break;

			default:
				$this->currentDirective = $directive;

				if (!empty($this->userAgent)) {
					$this->rules[$this->userAgent][$this->currentDirective][] = $value;
				}

				break;
		}
	}
}
