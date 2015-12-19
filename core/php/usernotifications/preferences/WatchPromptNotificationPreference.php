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
class WatchPromptNotificationPreference extends \BaseUserNotificationPreference {
	
	public function getLabel() { return 'Send emails when something I watch is running out of future events'; }

	public function getUserNotificationPreferenceType() { return 'WatchPrompt'; }
	
}

