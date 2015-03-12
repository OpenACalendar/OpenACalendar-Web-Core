<?php
use models\SiteModel;
use models\UserAccountModel;
use repositories\SiteRepository;
use repositories\UserAccountRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class SiteRepositoryBuilderUserInterestedInTest extends \PHPUnit_Framework_TestCase {

	function testBasic() {

		$DB = getNewTestDB();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());

		// User who added site is there
		$srb = new \repositories\builders\SiteRepositoryBuilder();
		$srb->setUserInterestedIn($user);
		$sites = $srb->fetchAll();
		$this->assertEquals(1, count($sites));

		// random user is not
		$srb = new \repositories\builders\SiteRepositoryBuilder();
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

	}

	function testUserWatchesSite() {

		$DB = getNewTestDB();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());

		$userWatchesSiteRepo = new \repositories\UserWatchesSiteRepository();
		$userWatchesSiteRepo->startUserWatchingSite($userTest, $site);

		// Test user who watches site has it!
		$srb = new \repositories\builders\SiteRepositoryBuilder();
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();

		$this->assertEquals(1, count($sites));

	}

	function testUserIsUserGroup() {

		$DB = getNewTestDB();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());


		$usrb = new \repositories\builders\UserGroupRepositoryBuilder();
		$usrb->setSite($site);
		$userGroups = $usrb->fetchAll();
		$this->assertTrue(count($userGroups) > 0);
		$userGroup = $userGroups[0];

		$uiugr = new \repositories\UserGroupRepository();
		$uiugr->addUserToGroup($userTest, $userGroup);

		// Test user in user group has it
		$srb = new \repositories\builders\SiteRepositoryBuilder();
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();

		$this->assertEquals(1, count($sites));

	}

}
