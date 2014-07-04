<?php

use models\UserAccountModel;
use models\SiteModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use models\EventModel;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteCloseTest extends \PHPUnit_Framework_TestCase {
	
	function testEventsVanish() {
		$DB = getNewTestDB();

		
		## User, Site, Event
		\TimeSource::mock(2014,1,1,1,2,3);
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
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);
	
		## Event can be found
		$erb = new EventRepositoryBuilder();
		$erb->setIncludeEventsFromClosedSites(true);
		$erb->fetchAll();
		$this->assertEquals(1, count($erb->fetchAll()));
		
		
		$erb = new EventRepositoryBuilder();
		$erb->setIncludeEventsFromClosedSites(false);
		$erb->fetchAll();
		$this->assertEquals(1, count($erb->fetchAll()));
		
		## Close Site
		\TimeSource::mock(2014,2,1,1,2,3);
		$site->setIsClosedBySysAdmin(true);
		$site->setClosedBySysAdminreason('Testing');
		$siteRepo->edit($site, $user);
		
		## Event can not be found
		$erb = new EventRepositoryBuilder();
		$erb->setIncludeEventsFromClosedSites(true);
		$erb->fetchAll();
		$this->assertEquals(1, count($erb->fetchAll()));
		
		
		$erb = new EventRepositoryBuilder();
		$erb->setIncludeEventsFromClosedSites(false);
		$erb->fetchAll();
		$this->assertEquals(0, count($erb->fetchAll()));
		
	}
	
}


