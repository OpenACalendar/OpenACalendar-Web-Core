<?php

use models\UserAccountModel;
use models\SiteModel;
use models\API2ApplicationModel;
use models\API2ApplicationUserPermissionsModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\API2ApplicationRepository;
use repositories\UserInAPI2ApplicationRepository;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserInAPI2ApplicationRepositoryTest extends \PHPUnit_Framework_TestCase {
	
	function testStartGrantedThenRemovePermissionIsWriteCalendar() {
		
		$DB = getNewTestDB();

		$userAdmin = new UserAccountModel();
		$userAdmin->setEmail("admin@jarofgreen.co.uk");
		$userAdmin->setUsername("admin");
		$userAdmin->setPassword("password");
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($userAdmin);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userAdmin, array(), getSiteQuotaUsedForTesting());
		
		$api2appRepo = new API2ApplicationRepository();
		$api2app = $api2appRepo->create($userAdmin, "Title");
		
		$userInApi2AppRepo = new UserInAPI2ApplicationRepository();
		
		#### Initial Set
		$permissions = new API2ApplicationUserPermissionsModel();
		$permissions->setIsEditorGranted();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(true, $userInApp->getIsEditor());

		#### This should do nothing
		$permissions = new API2ApplicationUserPermissionsModel();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(true, $userInApp->getIsEditor());

		#### Then Remove
		$permissions = new API2ApplicationUserPermissionsModel();
		$permissions->setIsEditorRefused();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(false, $userInApp->getIsEditor());

		#### This should do nothing
		$permissions = new API2ApplicationUserPermissionsModel();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(false, $userInApp->getIsEditor());

	}
	
	function testStartRefusedThenGrantPermissionIsWriteCalendar() {
		
		$DB = getNewTestDB();

		$userAdmin = new UserAccountModel();
		$userAdmin->setEmail("admin@jarofgreen.co.uk");
		$userAdmin->setUsername("admin");
		$userAdmin->setPassword("password");
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($userAdmin);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userAdmin, array(), getSiteQuotaUsedForTesting());
		
		$api2appRepo = new API2ApplicationRepository();
		$api2app = $api2appRepo->create($userAdmin, "Title");
		
		$userInApi2AppRepo = new UserInAPI2ApplicationRepository();
		
		#### Initial Set
		$permissions = new API2ApplicationUserPermissionsModel();
		// no permissions at all ....
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(false, $userInApp->getIsEditor());

		#### This should do nothing
		$permissions = new API2ApplicationUserPermissionsModel();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(false, $userInApp->getIsEditor());

		#### Then Remove
		$permissions = new API2ApplicationUserPermissionsModel();
		$permissions->setIsEditorGranted();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(true, $userInApp->getIsEditor());

		#### This should do nothing
		$permissions = new API2ApplicationUserPermissionsModel();
		$userInApi2AppRepo->setPermissionsForUserInApp($permissions, $user, $api2app);
		
		#### Test
		$userInApp = $userInApi2AppRepo->loadByUserAndApplication($user, $api2app);
		$this->assertEquals(true, $userInApp->getIsEditor());

	}
	
}



