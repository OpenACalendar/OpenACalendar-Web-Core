<?php


namespace usernotifications;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpcomingEventsUserNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'UpcomingEvents';
	}

	function setUpcomingEvents($events) {
		$this->data['upcomingevents'] = array();
		foreach($events as $event) {
			$this->data['upcomingevents'][] = $event->getId();
		}
	}

	function setAllEvents($events) {
		$this->data['allevents'] = array();
		foreach($events as $event) {
			$this->data['allevents'][] = $event->getId();
		}
	}
	
	public function getNotificationText() {
		return "You have upcoming events";
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		return $CONFIG->getWebIndexDomainSecure().'/me/agenda';
	}
	
}

