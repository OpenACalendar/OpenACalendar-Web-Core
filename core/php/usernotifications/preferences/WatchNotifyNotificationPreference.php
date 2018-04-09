<?php


namespace usernotifications\preferences;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class WatchNotifyNotificationPreference extends \BaseUserNotificationPreference {
		
	public function getLabel() { return 'Send emails when something I watch changes'; }

	public function getUserNotificationPreferenceType() { return 'WatchNotify'; }

    public function isAboutEventsInterestedIn(): bool { return true; }
    public function isAboutEditsIn(): bool { return false; }

}

