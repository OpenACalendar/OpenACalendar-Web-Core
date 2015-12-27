<?php

namespace userpermissions;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportChangeUserPermission extends \BaseUserPermission {

	public function getUserPermissionKey() {
        // This is IMPORTURL_CHANGE & not IMPORT_CHANGE for historical reasons.
        return 'IMPORTURL_CHANGE';
    }

	public function isForSite() { return true; }

	public function requiresUser() { return true; }

	public function requiresEditorUser() { return true; }

	public function getParentPermissionsIDs() {
		return array(
			array('org.openacalendar','CALENDAR_CHANGE'),
		);
	}

}
