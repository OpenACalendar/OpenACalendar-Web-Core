<?php

use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use models\VenueModel;
use models\EventModel;
use models\CountryModelModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;
use repositories\EventRepository;
use repositories\VenueRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaDuplicateTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		\TimeSource::mock(2014,1,1,0,0,0);
		$DB = getNewTestDB();
		addCountriesToTestDB();
		$countryRepo = new CountryRepository();
		$areaRepo = new AreaRepository();
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), getSiteQuotaUsedForTesting());

		$area1 = new AreaModel();
		$area1->setTitle("test");
		$area1->setDescription("test test");

		$area2 = new AreaModel();
		$area2->setTitle("test this looks similar");
		$area2->setDescription("test test");


		$areaRepo->create($area1, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);
		$areaRepo->create($area2, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);

		$areaChild = new AreaModel();
		$areaChild->setTitle("child");
		$areaChild->setDescription("child");

		$areaRepo->create($areaChild, $area2, $site, $countryRepo->loadByTwoCharCode('GB') , $user);

		$area1 = $areaRepo->loadById($area1->getId());
		$area2 = $areaRepo->loadById($area2->getId());

		$countryRepo = new CountryRepository();
		$gb = $countryRepo->loadByTwoCharCode('GB');

		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setCountryId($gb->getId());
		$venue->setAreaId($area2->getId());

		$venueRepo = new VenueRepository();
		$venueRepo->create($venue, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event->setAreaId($area2->getId());

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		// Test before

		$venue = $venueRepo->loadById($venue->getId());
		$this->assertEquals($area2->getId(), $venue->getAreaId());

		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertEquals($area2->getId(), $event->getAreaId());

		$areaChild = $areaRepo->loadById($areaChild->getId());
		$this->assertEquals($area2->getId(), $areaChild->getParentAreaId());

		// Mark
		\TimeSource::mock(2014,1,1,2,0,0);
		$areaRepo->markDuplicate($area2, $area1, $user);


		// Test Duplicate

		$venue = $venueRepo->loadById($venue->getId());
		$this->assertEquals($area1->getId(), $venue->getAreaId());

		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertEquals($area1->getId(), $event->getAreaId());

		$areaChild = $areaRepo->loadById($areaChild->getId());
		$this->assertEquals($area1->getId(), $areaChild->getParentAreaId());
	}
}




