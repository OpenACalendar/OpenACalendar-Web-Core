<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use models\VenueModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\UserHasNoEditorPermissionsInSiteRepository;
use repositories\VenueRepository;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserHasNoEditorPermissionsInSiteTest extends \BaseAppWithDBTest {

	function testAddAndRemove() {

		$this->addCountriesToTestDB();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);


		$siteModel = new \models\SiteModel();
		$siteModel->setTitle("Test");
		$siteModel->setSlug("test");

		$siteRepository = new \repositories\SiteRepository($this->app);
		$countryRepository = new \repositories\CountryRepository($this->app);
		$siteRepository->create($siteModel, $user, array($countryRepository->loadByTwoCharCode("GB")), $this->getSiteQuotaUsedForTesting(), true);

		// ########################################## Not there

		$userHasNoEditorPermissionsInSiteRepo = new UserHasNoEditorPermissionsInSiteRepository($this->app);

		$this->assertFalse($userHasNoEditorPermissionsInSiteRepo->isUserInSite($user, $siteModel));

		$userAccountRepoBuilder = new \repositories\builders\UserAccountRepositoryBuilder($this->app);
		$userAccountRepoBuilder->setUserHasNoEditorPermissionsInSite($siteModel);
		$this->assertEquals(0, count($userAccountRepoBuilder->fetchAll()));

		// ########################################## Add

		$userHasNoEditorPermissionsInSiteRepo->addUserToSite($user, $siteModel);


		// ########################################## There

		$this->assertTrue($userHasNoEditorPermissionsInSiteRepo->isUserInSite($user, $siteModel));


		$userAccountRepoBuilder = new \repositories\builders\UserAccountRepositoryBuilder($this->app);
		$userAccountRepoBuilder->setUserHasNoEditorPermissionsInSite($siteModel);
		$this->assertEquals(1, count($userAccountRepoBuilder->fetchAll()));

		// ########################################## Remove

		$userHasNoEditorPermissionsInSiteRepo->removeUserFromSite($user, $siteModel);

		// ########################################## There

		$this->assertFalse($userHasNoEditorPermissionsInSiteRepo->isUserInSite($user, $siteModel));

		$userAccountRepoBuilder = new \repositories\builders\UserAccountRepositoryBuilder($this->app);
		$userAccountRepoBuilder->setUserHasNoEditorPermissionsInSite($siteModel);
		$this->assertEquals(0, count($userAccountRepoBuilder->fetchAll()));
		
	}


}
