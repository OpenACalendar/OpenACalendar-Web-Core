<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserPermissionsListTest  extends \BaseAppTest {

	function testAnonymousCantHaveChangePermission() {


		$extensionManager = new \ExtensionManager($this->app);

		$extensionCore = new \ExtensionCore($this->app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), null, false, true);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCanHaveChangePermission() {


		$extensionManager = new \ExtensionManager($this->app);

		$extensionCore = new \ExtensionCore($this->app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(true);

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, false, true);

		$this->assertTrue( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCantHaveChangePermissionWhenUserNotEditor() {


		$extensionManager = new \ExtensionManager($this->app);

		$extensionCore = new \ExtensionCore($this->app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(false);

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, false, true);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCantHaveChangePermissionWhenReadOnly() {


		$extensionManager = new \ExtensionManager($this->app);

		$extensionCore = new \ExtensionCore($this->app);

		$permission = $extensionCore->getUserPermission("CALENDAR_CHANGE");

		$user = new \models\UserAccountModel();
		$user->setIsEditor(true);

		$userPermissionList = new UserPermissionsList($extensionManager, array($permission), $user, true, true);

		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_CHANGE") );
		$this->assertFalse( $userPermissionList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE") );
	}

	function testUserCanHasChangePermissionWhenHasAdminPermission() {


		$extensionManager = new \ExtensionManager($this->app);

		$extensionCore = new \ExtensionCore($this->app);

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

