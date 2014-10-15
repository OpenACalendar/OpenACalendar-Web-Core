<?php

use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountResetRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserPermissionsIndexTest extends \PHPUnit_Framework_TestCase {

	function testAllUsersCreateSiteByDefault() {
		global $CONFIG;
		$CONFIG->canCreateSitesVerifiedEditorUsers = true;
		$DB = getNewTestDB();
		$app = getNewTestApp();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->verifyEmail($user);

		// reload user object so all flags set correctly
		$user = $userRepo->loadByUserName("test");

		$extensionsManager = new ExtensionManager($app);
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


	function testAllUsersCreateSite() {
		global $CONFIG;
		$CONFIG->canCreateSitesVerifiedEditorUsers = false;
		$CONFIG->newUsersAreEditors = true;
		$DB = getNewTestDB();
		$app = getNewTestApp();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($user);

		// reload user object so all flags set correctly
		$user = $userRepo->loadByUserName("test");

		$extensionsManager = new ExtensionManager($app);
		$userPerRepo = new \repositories\UserPermissionsRepository($extensionsManager);

		## Noone can create sites

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user);
		$this->assertEquals(0, count($permissions->getPermissions()));

		## Now create user group for all users

		$userGroupModel = new \models\UserGroupModel();
		$userGroupModel->setTitle("TITLE");
		$userGroupModel->setIsIncludesUsers(true);

		$userGroupRepo = new \repositories\UserGroupRepository();
		$userGroupRepo->createForIndex($userGroupModel);

		$userGroupRepo->addPermissionToGroup(new \userpermissions\CreateSiteUserPermission(), $userGroupModel, null);

		## Now user can create sites, anon can't!

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, false);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user, false);
		$this->assertEquals(1, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

	}

	function testAllVerifiedUsersCreateSite() {
		global $CONFIG;
		$CONFIG->canCreateSitesVerifiedEditorUsers = false;
		$CONFIG->newUsersAreEditors = true;
		$DB = getNewTestDB();
		$app = getNewTestApp();

		$userVerified = new UserAccountModel();
		$userVerified->setEmail("verified@jarofgreen.co.uk");
		$userVerified->setUsername("verified");
		$userVerified->setPassword("password");

		$userUnverified = new UserAccountModel();
		$userUnverified->setEmail("unverified@jarofgreen.co.uk");
		$userUnverified->setUsername("unverified");
		$userUnverified->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($userVerified);
		$userRepo->verifyEmail($userVerified);
		$userRepo->create($userUnverified);

		// reload user object so all flags set correctly
		$userVerified = $userRepo->loadByUserName($userVerified->getUsername());
		$userUnverified = $userRepo->loadByUserName($userUnverified->getUsername());


		// reload user object so all flags set correctly
		$user = $userRepo->loadByUserName("test");

		$extensionsManager = new ExtensionManager($app);
		$userPerRepo = new \repositories\UserPermissionsRepository($extensionsManager);

		## Noone can create sites

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($userVerified);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($userUnverified);
		$this->assertEquals(0, count($permissions->getPermissions()));

		## Now create user group for all users

		$userGroupModel = new \models\UserGroupModel();
		$userGroupModel->setTitle("TITLE");
		$userGroupModel->setIsIncludesVerifiedUsers(true);

		$userGroupRepo = new \repositories\UserGroupRepository();
		$userGroupRepo->createForIndex($userGroupModel);

		$userGroupRepo->addPermissionToGroup(new \userpermissions\CreateSiteUserPermission(), $userGroupModel, null);

		## Now user can create sites, anon can't!

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, false);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($userVerified, false);
		$this->assertEquals(1, count($permissions->getPermissions()));


		$permissions = $userPerRepo->getPermissionsForUserInIndex($userVerified, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($userUnverified, false);
		$this->assertEquals(0, count($permissions->getPermissions()));


		$permissions = $userPerRepo->getPermissionsForUserInIndex($userUnverified, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

	}

	function testSpecificUsersCreateSite() {
		global $CONFIG;
		$CONFIG->canCreateSitesVerifiedEditorUsers = false;
		$CONFIG->newUsersAreEditors = true;
		$DB = getNewTestDB();
		$app = getNewTestApp();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userOther = new UserAccountModel();
		$userOther->setEmail("other@jarofgreen.co.uk");
		$userOther->setUsername("other");
		$userOther->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->verifyEmail($user);
		$userRepo->create($userOther);
		$userRepo->verifyEmail($userOther);

		// reload user object so all flags set correctly
		$userOther = $userRepo->loadByUserName($userOther->getUsername());
		$user = $userRepo->loadByUserName("test");

		$extensionsManager = new ExtensionManager($app);
		$userPerRepo = new \repositories\UserPermissionsRepository($extensionsManager);

		## Noone can create sites

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($userOther);
		$this->assertEquals(0, count($permissions->getPermissions()));

		## Now create user group for all users

		$userGroupModel = new \models\UserGroupModel();
		$userGroupModel->setTitle("TITLE");

		$userGroupRepo = new \repositories\UserGroupRepository();
		$userGroupRepo->createForIndex($userGroupModel);
		$userGroupRepo->addUserToGroup($user, $userGroupModel);

		$userGroupRepo->addPermissionToGroup(new \userpermissions\CreateSiteUserPermission(), $userGroupModel, null);

		## Now user can create sites, anon can't!

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, false);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex(null, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($user, false);
		$this->assertEquals(1, count($permissions->getPermissions()));


		$permissions = $userPerRepo->getPermissionsForUserInIndex($user, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

		$permissions = $userPerRepo->getPermissionsForUserInIndex($userOther, false);
		$this->assertEquals(0, count($permissions->getPermissions()));


		$permissions = $userPerRepo->getPermissionsForUserInIndex($userOther, true);
		$this->assertEquals(0, count($permissions->getPermissions()));

	}

}
