<?php

/**
 * Class for parsing robots.txt files
 *
 * @author Eugene Yurkevich (bopodaa@gmail.com)
 *
 *
 * Some useful links and materials:
 * @link https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt
 * @link https://help.yandex.com/webmaster/controlling-robot/robots-txt.xml
 */
class RobotsTxtParser
{
	// default encoding
	const DEFAULT_ENCODING = 'UTF-8';

	// states
	const STATE_ZERO_POINT = 'zero-point';
	const STATE_READ_DIRECTIVE = 'read-directive';
	const STATE_SKIP_SPACE = 'skip-space';
	const STATE_SKIP_LINE = 'skip-line';
	const STATE_READ_VALUE = 'read-value';

	// directives
	const DIRECTIVE_ALLOW = 'allow';
	const DIRECTIVE_DISALLOW = 'disallow';
	const DIRECTIVE_HOST = 'host';
	const DIRECTIVE_SITEMAP = 'sitemap';
	const DIRECTIVE_USERAGENT = 'user-agent';
	const DIRECTIVE_CRAWL_DELAY = 'crawl-delay';
	const DIRECTIVE_CLEAN_PARAM = 'clean-param';

	/**
	 * Default user-agent
	 * First off, links should be checked by specific user-agent rules. If specific user-agent isn't specified than default user-agent used.
	 */
	const USER_AGENT_ALL = '*';

	// current state
	private $state = '';

	// robots.txt file content
	private $content = '';

	// rules set
	private $rules = array();

	// internally used variables
	private $current_word = '';
	private $current_char = '';
	private $char_index = 0;
	private $current_directive = '';
	private $userAgent = self::USER_AGENT_ALL;

	/**
	 * @param  string $content - file content
	 * @param  string $encoding - encoding
	 * @return RobotsTxtParser
	 */
	public function __construct($content, $encoding = self::DEFAULT_ENCODING)
	{
		// convert encoding
		$encoding = !empty($encoding) ? $encoding : mb_detect_encoding($content);
		mb_internal_encoding($encoding);

		// set content
		$this->content = iconv($encoding, 'UTF-8//IGNORE', $content);
		$this->content .= "\n";

		// set default state
		$this->state = self::STATE_ZERO_POINT;

		// parse rules - default state
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
				return array();
			}
		}
	}

	/**
	 * Get sitemaps links.
	 * Sitemap always relates to all user-agents and return in rules with user-agent "*"
	 *
	 * @return array  all sitemap urls
	 */
	public function getSitemaps()
	{
		$rules = $this->getRules(self::USER_AGENT_ALL);
		if (!empty($rules[self::DIRECTIVE_SITEMAP])) {
			return $rules[self::DIRECTIVE_SITEMAP];
		}

		return array();
	}

	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Comment signal (#)
	 */
	private function sharp()
	{
		return ($this->current_char == '#');
	}

	/**
	 * Allow directive signal
	 */
	private function directiveAllow()
	{
		return ($this->current_directive == self::DIRECTIVE_ALLOW);
	}

	/**
	 * Disallow directive signal
	 */
	private function directiveDisallow()
	{
		return ($this->current_directive == self::DIRECTIVE_DISALLOW);
	}

	/**
	 * Host directive signal
	 */
	private function directiveHost()
	{
		return ($this->current_directive == self::DIRECTIVE_HOST);
	}

	/**
	 * Sitemap directive signal
	 */
	private function directiveSitemap()
	{
		return ($this->current_directive == self::DIRECTIVE_SITEMAP);
	}

	/**
	 * Clean-param directive signal
	 */
	private function directiveCleanParam()
	{
		return ($this->current_directive == self::DIRECTIVE_CLEAN_PARAM);
	}

	/**
	 * User-agent directive signal
	 */
	private function directiveUserAgent()
	{
		return ($this->current_directive == self::DIRECTIVE_USERAGENT);
	}

	/**
	 * Crawl-Delay directive signal
	 */
	private function directiveCrawlDelay()
	{
		return ($this->current_directive == self::DIRECTIVE_CRAWL_DELAY);
	}

	/**
	 * Key : value pair separator signal
	 */
	private function lineSeparator()
	{
		return ($this->current_char == ':');
	}

	/**
	 * Move to new line signal
	 */
	private function newLine()
	{
		$asciiCode = ord($this->current_char);

		return ($this->current_char == "\n"
			|| $asciiCode == 13
			|| $asciiCode == 10
			|| $this->current_word == "\r\n"
			|| $this->current_word == "\n\r"
		);
	}

	/**
	 * "Space" signal
	 */
	private function space()
	{
		return ($this->current_char == "\s");
	}

	/**
	 * Change state
	 *
	 * @param string $stateTo - state that should be set
	 * @return void
	 */
	private function switchState($stateTo = self::STATE_SKIP_LINE)
	{
		$this->state = $stateTo;
	}

	/**
	 * Parse rules
	 *
	 * @return void
	 */
	public function prepareRules()
	{
		$contentLength = mb_strlen($this->content);
		while ($this->char_index <= $contentLength) {
			$this->step();
		}

		foreach ($this->rules as $userAgent => $directive) {
			foreach ($directive as $directiveName => $directiveValue) {
				if (is_array($directiveValue)) {
					$this->rules[$userAgent][$directiveName] = array_values(array_unique($directiveValue));
				}
			}
		}
	}

	/**
	 * Check if we should switch
	 * @return bool
	 */
	private function shouldSwitchToZeroPoint()
	{
		return in_array(strtolower($this->current_word), array(
			self::DIRECTIVE_ALLOW,
			self::DIRECTIVE_DISALLOW,
			self::DIRECTIVE_HOST,
			self::DIRECTIVE_USERAGENT,
			self::DIRECTIVE_SITEMAP,
			self::DIRECTIVE_CRAWL_DELAY,
			self::DIRECTIVE_CLEAN_PARAM,
		), true);
	}

	/**
	 * Process state ZERO_POINT
	 * @return RobotsTxtParser
	 */
	private function zeroPoint()
	{
		if ($this->shouldSwitchToZeroPoint()) {
			$this->switchState(self::STATE_READ_DIRECTIVE);
		} // unknown directive - skip it
		elseif ($this->newLine()) {
			$this->current_word = "";
			$this->increment();
		}
		else {
			$this->increment();
		}
		return $this;
	}

	/**
	 * Read directive
	 * @return RobotsTxtParser
	 */
	private function readDirective()
	{
		$this->current_directive = strtolower(trim($this->current_word));

		$this->increment();

		if ($this->lineSeparator()) {
			$this->current_word = "";
			$this->switchState(self::STATE_READ_VALUE);
		}
		else {
			if ($this->space()) {
				$this->switchState(self::STATE_SKIP_SPACE);
			}
			if ($this->sharp()) {
				$this->switchState(self::STATE_SKIP_LINE);
			}
		}
		return $this;
	}

	/**
	 * Skip space
	 * @return RobotsTxtParser
	 */
	private function skipSpace()
	{
		$this->char_index++;
		$this->current_word = mb_substr($this->current_word, -1);
		return $this;
	}

	/**
	 * Skip line
	 * @return RobotsTxtParser
	 */
	private function skipLine()
	{
		$this->char_index++;
		$this->switchState(self::STATE_ZERO_POINT);
		return $this;
	}

	/**
	 * Read value
	 * @return RobotsTxtParser
	 */
	private function readValue()
	{
		if ($this->newLine()) {
			$this->assignValueToDirective();
		}
		elseif ($this->sharp()) {
			$this->current_word = mb_substr($this->current_word, 0, -1);
			$this->assignValueToDirective();
		}
		else {
			$this->increment();
		}
		return $this;
	}

	private function assignValueToDirective()
	{
		if ($this->directiveUserAgent()) {
			$this->userAgent = mb_strtolower(trim($this->current_word));
			if (!isset($this->rules[$this->userAgent])) {
				$this->rules[$this->userAgent] = array();
			}
		}
		elseif ($this->directiveCrawlDelay()) {
			$this->rules[$this->userAgent][$this->current_directive] = (double)$this->current_word;
		}
		elseif ($this->directiveSitemap()) {
			$this->rules[self::USER_AGENT_ALL][$this->current_directive][] = $this->current_word;
		}
		elseif ($this->directiveCleanParam()) {
			$this->rules[$this->userAgent][$this->current_directive][] = trim($this->current_word);
		}
		elseif ($this->directiveHost()) {
			if (empty($this->rules['*'][$this->current_directive])) { // save only first host directive value, assign to '*'
				$this->rules['*'][$this->current_directive] = $this->current_word;
			}
		}
		else {
			if (!empty($this->current_word)) {
				$this->rules[$this->userAgent][$this->current_directive][] = $this->current_word;
			}
		}
		$this->current_word = '';
		$this->current_directive = '';
		$this->switchState(self::STATE_ZERO_POINT);
	}

	/**
	 * Machine step
	 *
	 * @return void
	 */
	private function step()
	{
		switch ($this->state) {
			case self::STATE_ZERO_POINT:
				$this->zeroPoint();
				break;

			case self::STATE_READ_DIRECTIVE:
				$this->readDirective();
				break;

			case self::STATE_SKIP_SPACE:
				$this->skipSpace();
				break;

			case self::STATE_SKIP_LINE:
				$this->skipLine();
				break;

			case self::STATE_READ_VALUE:
				$this->readValue();
				break;
		}
	}

	/**
	 * Move to the following step
	 *
	 * @return void
	 */
	private function increment()
	{
		$this->current_char = mb_substr($this->content, $this->char_index, 1);
		$this->current_word .= $this->current_char;
		if (!$this->directiveCleanParam() && !$this->directiveUserAgent()) {
			$this->current_word = trim($this->current_word);
		}
		$this->char_index++;
	}
}
