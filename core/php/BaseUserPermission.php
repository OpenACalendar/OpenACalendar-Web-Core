<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseUserPermission {

	public function getUserPermissionExtensionID() {
		return 'org.openacalendar';
	}

	public abstract function getUserPermissionKey();


	public function isForIndex() { return false; }

	public function isForSite() { return false; }

	public function requiresUser() { return false; }

	public function requiresVerifiedUser() { return false; }

	public function requiresEditorUser() { return false; }


	/**
	 *
	 * If a user has a parent permission they are deemed to have the child permission to.
	 * EG a user with the CALENDAR_ADMINISTRATE permission also has the CALENDAR_CHANGE permission.
	 *
	 * @return array of ("ext id","permission key")
	 */
	public function getParentPermissionsIDs() { return array(); }

}

