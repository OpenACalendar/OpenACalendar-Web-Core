<?php

namespace org\openacalendar\curatedlists\userpermissions;


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class CuratedListsChangeUserPermission extends \BaseUserPermission {

    public function getUserPermissionExtensionID() {
        return 'org.openacalendar.curatedlists';
    }

    public function getUserPermissionKey() { return 'CURATED_LISTS_CHANGE'; }

    public function isForSite() { return true; }

    public function requiresUser() { return true; }

    public function requiresEditorUser() { return true; }

    public function getParentPermissionsIDs() {
        return array(
            array('org.openacalendar','CALENDAR_CHANGE'),
        );
    }

}
