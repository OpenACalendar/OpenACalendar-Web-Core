<?php


namespace pingback;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class PingBack {

	protected $source_url;

	protected $target_url;

	function __construct($source_url, $target_url)
	{
		$this->source_url = $source_url;
		$this->target_url = $target_url;
	}

	/**
	 * @return mixed
	 */
	public function getSourceUrl()
	{
		return $this->source_url;
	}

	/**
	 * @return mixed
	 */
	public function getTargetUrl()
	{
		return $this->target_url;
	}

}
