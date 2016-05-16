<?php

namespace org\openacalendar\meetup;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MeetupURLParser {

	protected $eventId;
	protected $groupName;


	function __construct($url) {

		$urlBits = parse_url($url);

		if (isset($urlBits['host']) && in_array(strtolower($urlBits['host']), array('meetup.com','www.meetup.com'))) {

			$bits = explode("/", $urlBits['path']);

			if (count($bits) <= 3) {
				$this->groupName = $bits[1];
				return true;
			} else if (count($bits) > 3 && $bits[2] == 'events') {
				$this->groupName = $bits[1];
				$this->eventId = $bits[3];
				return true;
			}

		}

	}

	/**
	 * @return mixed
	 */
	public function getEventId() {
		return $this->eventId;
	}

	/**
	 * @return mixed
	 */
	public function getGroupName() {
		return $this->groupName;
	}

}