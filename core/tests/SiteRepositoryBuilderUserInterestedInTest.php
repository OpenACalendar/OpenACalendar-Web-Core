<?php
use models\SiteModel;
use models\UserAccountModel;
use repositories\CountryRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class SiteRepositoryBuilderUserInterestedInTest extends \BaseAppWithDBTest {

	function testBasic() {


		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		// User who added site is there
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($user);
		$sites = $srb->fetchAll();
		$this->assertEquals(1, count($sites));

		// random user is not
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

	}

	function testUserWatchesSite() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		// Test user doesn't have it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

		// watch site
		$userWatchesSiteRepo = new \repositories\UserWatchesSiteRepository($this->app);
		$userWatchesSiteRepo->startUserWatchingSite($userTest, $site);

		// has it!
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();

		$this->assertEquals(1, count($sites));

	}

	function testUserWatchesGroup() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$group = new \models\GroupModel();
		$group->setTitle("Test");

		$groupRepo = new \repositories\GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);

		// Test user doesn't have it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

		// watch group
		$userWatchesGroupRepo = new \repositories\UserWatchesGroupRepository($this->app);
		$userWatchesGroupRepo->startUserWatchingGroup($userTest, $group);

		// has it!
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(1, count($sites));

	}
	
	function testUserWatchesArea() {
		$this->addCountriesToTestDB();
		$countryRepo = new CountryRepository($this->app);


		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$area = new \models\AreaModel();
		$area->setTitle("Test");

		$areaRepo = new \repositories\AreaRepository($this->app);
		$areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);

		// Test user doesn't have it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

		// watch area
		$userWatchesAreaRepo = new \repositories\UserWatchesAreaRepository($this->app);
		$userWatchesAreaRepo->startUserWatchingArea($userTest, $area);

		// has it!
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(1, count($sites));

	}

	function testUserIsUserGroup() {


		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());


		$usrb = new \repositories\builders\UserGroupRepositoryBuilder($this->app);
		$usrb->setSite($site);
		$userGroups = $usrb->fetchAll();
		$this->assertTrue(count($userGroups) > 0);
		$userGroup = $userGroups[0];

		// Test user doesn't have it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

		// added to user group
		$uiugr = new \repositories\UserGroupRepository($this->app);
		$uiugr->addUserToGroup($userTest, $userGroup);

		//  has it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(1, count($sites));

	}

	function testUserIsInterestedInSite() {


		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userTest = new UserAccountModel();
		$userTest->setEmail("testtest@jarofgreen.co.uk");
		$userTest->setUsername("testtest");
		$userTest->setPassword("password");


		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		$userRepo->create($userTest);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());


		$usrb = new \repositories\builders\UserGroupRepositoryBuilder($this->app);
		$usrb->setSite($site);
		$userGroups = $usrb->fetchAll();
		$this->assertTrue(count($userGroups) > 0);
		$userGroup = $userGroups[0];

		// Test user doesn't have it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(0, count($sites));

		// interested
		$uiisr = new \repositories\UserInterestedInSiteRepository($this->app);
		$uiisr->markUserInterestedInSite($userTest, $site);

		//  has it
		$srb = new \repositories\builders\SiteRepositoryBuilder($this->app);
		$srb->setUserInterestedIn($userTest);
		$sites = $srb->fetchAll();
		$this->assertEquals(1, count($sites));

	}

}
