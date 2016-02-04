<?php

use models\VenueHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\VenueModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\VenueRepository;
use repositories\VenueHistoryRepository;
use repositories\CountryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueHistoryWithDBTest extends \BaseAppWithDBTest {

	function testIntegration1() {

		$this->addCountriesToTestDB();
		$this->app['timesource']->mock(2014, 1, 1, 12, 0, 0);
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$countryRepo = new CountryRepository($this->app);
		$gb = $countryRepo->loadByTwoCharCode('GB');
		
		## Create venue
		$this->app['timesource']->mock(2014, 1, 1, 13, 0, 0);
		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setCountryId($gb->getId());
		
		$venueRepo = new VenueRepository($this->app);
		$venueRepo->create($venue, $site, $user);
		
		## Edit venue
		$this->app['timesource']->mock(2014, 1, 1, 14, 0, 0);
		
		$venue = $venueRepo->loadById($venue->getId());
		$venue->setDescription("testy");
		$venue->setLat(3.6);
		$venue->setLng(3.7);
		$venueRepo->edit($venue, $user);
		
		## Now save changed flags on these .....
		$venueHistoryRepo = new VenueHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM venue_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$venueHistory = new VenueHistoryModel();
			$venueHistory->setFromDataBaseRow($data);
			$venueHistoryRepo->ensureChangedFlagsAreSet($venueHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder($this->app);
		$historyRepo->setVenue($venue);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeGroupHistory(false);
		$historyRepo->setIncludeVenueHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getCountryIdChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
		$this->assertEquals(true, $histories[0]->getLatChanged());		
		$this->assertEquals(true, $histories[0]->getLngChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(true, $histories[1]->getCountryIdChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());
		$this->assertEquals(false, $histories[1]->getLatChanged());		
		$this->assertEquals(false, $histories[1]->getLngChanged());		
	}


	function testIntegration2() {
		$this->addCountriesToTestDB();
		$this->app['timesource']->mock(2014, 1, 1, 12, 0, 0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$countryRepo = new CountryRepository($this->app);
		$gb = $countryRepo->loadByTwoCharCode('GB');

		## Create venue
		$this->app['timesource']->mock(2014, 1, 1, 13, 0, 0);
		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setCountryId($gb->getId());

		$venueRepo = new VenueRepository($this->app);
		$venueRepo->create($venue, $site, $user);

		## Edit venue
		$this->app['timesource']->mock(2014, 1, 1, 14, 0, 0);

		$venue = $venueRepo->loadById($venue->getId());
		$venue->setDescription("testy");
		$venue->setLat(3.6);
		$venue->setLng(3.7);
		$venueRepo->edit($venue, $user);

		## Delete venue
		$this->app['timesource']->mock(2014, 1, 1, 15, 0, 0);

		$venueRepo->delete($venue, $user);

		## Now save changed flags on these .....
		$venueHistoryRepo = new VenueHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM venue_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$venueHistory = new VenueHistoryModel();
			$venueHistory->setFromDataBaseRow($data);
			$venueHistoryRepo->ensureChangedFlagsAreSet($venueHistory);
		}

		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder($this->app);
		$historyRepo->setVenue($venue);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeGroupHistory(false);
		$historyRepo->setIncludeVenueHistory(true);
		$histories = $historyRepo->fetchAll();

		$this->assertEquals(3, count($histories));

		#the delete
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(false, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getCountryIdChanged());
		$this->assertEquals(true, $histories[0]->getIsDeletedChanged());
		$this->assertEquals(false, $histories[0]->getLatChanged());
		$this->assertEquals(false, $histories[0]->getLngChanged());

		#the edit
		$this->assertEquals(FALSE, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(false, $histories[1]->getCountryIdChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());
		$this->assertEquals(true, $histories[1]->getLatChanged());
		$this->assertEquals(true, $histories[1]->getLngChanged());

		#the create
		$this->assertEquals(true, $histories[2]->getTitleChanged());
		$this->assertEquals(true, $histories[2]->getDescriptionChanged());
		$this->assertEquals(true, $histories[2]->getCountryIdChanged());
		$this->assertEquals(false, $histories[2]->getIsDeletedChanged());
		$this->assertEquals(false, $histories[2]->getLatChanged());
		$this->assertEquals(false, $histories[2]->getLngChanged());



	}

	
}

