<?php

use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountResetRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserPermissionsIndexConfigTrueTest extends \BaseAppWithDBTest {


	protected function setConfig(\Config $config) {
		$config->canCreateSitesVerifiedEditorUsers = true;
	}

	function testAllUsersCreateSiteByDefault() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->verifyEmail($user);

		// reload user object so all flags set correctly
		$user = $userRepo->loadByUserName("test");

		$extensionsManager = new ExtensionManager($this->app);
		$userPerRepo = new \repositories\UserPermissionsRepository($extensionsManager);

		## user can create sites, anon can't!

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, false);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user, false);
		$this->assertEquals(1, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user, true);
		$this->assertEquals(0, count($permissions->getPermissions()));



	}

}
