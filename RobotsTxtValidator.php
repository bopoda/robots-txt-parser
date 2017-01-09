<?php

/**
 * Class RobotsTxtValidator
 * Class designed to check is url allowed or not to crawl by specific user-agent according to robots.txt rules.
 */
class RobotsTxtValidator
{
	/**
	 * @var array
	 */
	private $orderedDirectives;
	/**
	 * RobotsTxtValidator constructor
	 *
	 * @param array $rules  Array of all rules from class RobotsTxtParser
	 */
	public function __construct(array $rules)
	{
		foreach($rules as $userAgent => $rulesByUserAgent) {
			$this->orderedDirectives[$userAgent] = $this->orderDirectives($rules[$userAgent]);
		}
	}

	/**
	 * Return true if url is allow to crawl by robots.txt rules otherwise false
	 *
	 * @param string $url
	 * @param string $userAgent
	 * @return bool
	 */
	public function isUrlAllow($url, $userAgent = '*')
	{
		$relativeUrl = $this->getRelativeUrl($url);

		// if has not allow rules we can determine when url disallowed even on one coincidence - just to do it faster.
		$hasAllowDirectives = true;
		foreach ($this->orderedDirectives[$userAgent] as $directiveRow) {			
			if ($directiveRow['directive'] == 'allow' ) {
				$hasAllowDirectives = true;
				break;
			}
		}

		$isAllow = true;
		foreach ($this->orderedDirectives[$userAgent] as $directiveRow) {			
			if (!in_array($directiveRow['directive'], array('allow', 'disallow'))) {
				continue;
			}

			if (preg_match($directiveRow['rule_regexp'], $relativeUrl)) {
				if ($directiveRow['directive'] == 'allow' ) {
					$isAllow = true;
				}
				else {
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
		if (!empty($this->rules[$userAgent])) {
			return $this->orderDirectives($this->rules[$userAgent]);
		}

		return array();
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
	 * @throws Exception
	 */
	private function getRelativeUrl($url)
	{
		if (!$url) {
			throw new Exception('Url should not be empty');
		}

		if (!preg_match('!^https?://!i', $url)) {
			if (empty($url[0]) || $url[0] !== '/') {
				throw new Exception('Url should start from "/" or has protocol with domain, got ' . $url);
			}
			else {
				return $url;
			}
		}

		return parse_url($url, PHP_URL_PATH);
	}

	/**
	 * Convert robots.txt rule to php RegExp
	 *
	 * @param string $ruleValue
	 * @return string
	 */
	private static function prepareRegexpRule($ruleValue)
	{
		$replacements = array(
			'/\$./' => '\$',
			'/\?/' => '\?',
			'/\./' => '\.',
			'/\*/' => '.*',
		);

		$ruleValue = preg_replace(array_keys($replacements), array_values($replacements), $ruleValue);

		$regexp = '/^' . str_replace('/', '\/', $ruleValue) . '/i';
		return str_replace('\\\\/', '\/', $regexp);
	}
}