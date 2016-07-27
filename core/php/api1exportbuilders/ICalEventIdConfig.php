<?php
namespace api1exportbuilders;

/**
 *
 * This was at one stage used for a Google Calendar import hack.
 * (See https://github.com/OpenACalendar/OpenACalendar-Web-Core/issues/176 )
 * However, now it's not needed and we may remove it.
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ICalEventIdConfig {

	protected $isSlug = false;

	protected $isSlugStartEnd = false;

	function __construct($option = null, $server = array()) {

		if (strtolower(trim($option)) == 'slug') {
			$this->isSlug = true;
			return;
		}

		if (strtolower(trim($option)) == 'slugstartend') {
			$this->isSlugStartEnd = true;
			return;
		}

		// Nothing selected. The Default.
		$this->isSlug = true;

	}

	/**
	 * @return boolean
	 */
	public function isSlug() {
		return $this->isSlug;
	}

	/**
	 * @return boolean
	 */
	public function isSlugStartEnd() {
		return $this->isSlugStartEnd;
	}

}
