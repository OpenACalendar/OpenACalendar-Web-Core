<?php
namespace api1exportbuilders;

/**
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

		if (is_array($server) && isset($server['HTTP_USER_AGENT'])) {
			if (trim($server['HTTP_USER_AGENT']) == 'Google-Calendar-Importer') {
				$this->isSlugStartEnd = true;
				return;
			}
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
