<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserPermissionsListTest  extends \PHPUnit_Framework_TestCase {

	function testAnonymousCantHaveChangePermission() {

		$app = getNewTestApp();

		$extensionManager = new \ExtensionManager($app);

		$extensionCore = new \ExtensionCore($app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), null, false, true);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCanHaveChangePermission() {

		$app = getNewTestApp();

		$extensionManager = new \ExtensionManager($app);

		$extensionCore = new \ExtensionCore($app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(true);

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, false, true);

		$this->assertTrue( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCantHaveChangePermissionWhenUserNotEditor() {

		$app = getNewTestApp();

		$extensionManager = new \ExtensionManager($app);

		$extensionCore = new \ExtensionCore($app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(false);

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, false, true);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCantHaveChangePermissionWhenReadOnly() {

		$app = getNewTestApp();

		$extensionManager = new \ExtensionManager($app);

		$extensionCore = new \ExtensionCore($app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(true);

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, true, true);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCanHasChangePermissionWhenHasAdminPermission() {

		$app = getNewTestApp();

		$extensionManager = new \ExtensionManager($app);

		$extensionCore = new \ExtensionCore($app);

		$permission = $extensionCore->getUserPermission("CALENDAR_ADMINISTRATE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(true);

		// With include child permissions

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, false, true);

		$this->assertTrue( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertTrue( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );

		// With not includeing child permissions

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, false, false);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertTrue( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}
}

