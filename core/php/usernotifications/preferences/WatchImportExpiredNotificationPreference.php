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
class WatchImportExpiredNotificationPreference extends \BaseUserNotificationPreference {
		
	public function getLabel() { return 'Send emails when something I watch has an importer that expires'; }
	
	public function getUserNotificationPreferenceType() { return 'WatchImportExpired'; }
	
}

