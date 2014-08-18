<?php

use models\UserAccountModel;
use models\SiteModel;
use models\VenueModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\VenueRepository;
use repositories\CountryRepository;
use repositories\EventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueDuplicateTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		\TimeSource::mock(2014,1,1,0,0,0);
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

		$venue1 = new VenueModel();
		$venue1->setTitle("test");
		$venue1->setDescription("test test");
		$venue1->setCountryId($gb->getId());

		$venue2 = new VenueModel();
		$venue2->setTitle("test this looks similar");
		$venue2->setDescription("test test");
		$venue2->setCountryId($gb->getId());

		$venueRepo = new VenueRepository();
		$venueRepo->create($venue1, $site, $user);
		$venueRepo->create($venue2, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event->setVenueId($venue2->getId());

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		// Test before
		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertEquals($venue2->getId(), $event->getVenueId());

		// Mark
		\TimeSource::mock(2014,1,1,2,0,0);
		$venueRepo->markDuplicate($venue2, $venue1, $user);

		// Test Duplicate
		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertEquals($venue1->getId(), $event->getVenueId());


	}


}




