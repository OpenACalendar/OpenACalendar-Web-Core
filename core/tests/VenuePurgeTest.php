<?php

use models\UserAccountModel;
use models\SiteModel;
use models\VenueModel;
use models\AreaModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\VenueRepository;
use repositories\EventRepository;
use repositories\CountryRepository;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenuePurgeTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		$DB = getNewTestDB();
		addCountriesToTestDB();

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
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		$countryRepo = new CountryRepository();
		$gb = $countryRepo->loadByTwoCharCode('GB');

		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");

		$areaRepo = new \repositories\AreaRepository();
		$areaRepo->create($area, null, $site, $gb , $user);


		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setCountryId($gb->getId());
		$venue->setAreaId($area->getId());

		$venueRepo = new VenueRepository();
		$venueRepo->create($venue, $site, $user);


		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event->setVenueId($venue->getId());

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		## Test
		$this->assertNotNull($venueRepo->loadBySlug($site, $venue->getSlug()));
		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertEquals($venue->getId(), $event->getVenueId());

		## Now Purge!
		$venueRepo->purge($venue);

		## Test
		$this->assertNull($venueRepo->loadBySlug($site, $venue->getSlug()));
		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertNull($event->getVenueId());

	}


	
}




