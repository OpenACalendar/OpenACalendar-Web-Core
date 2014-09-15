<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
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
class EventDuplicateTest extends \PHPUnit_Framework_TestCase {


	
	function test1() {
		$DB = getNewTestDB();

		TimeSource::mock(2014,5,1,7,0,0);
		
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
		
		$event1 = new EventModel();
		$event1->setSummary("test");
		$event1->setDescription("test test");
		$event1->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event1->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event1->setUrl("http://www.info.com");
		$event1->setTicketUrl("http://www.tickets.com");

		$event2 = new EventModel();
		$event2->setSummary("test this looks similar");
		$event2->setDescription("test test");
		$event2->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event2->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event2->setUrl("http://www.info.com");
		$event2->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event1, $site, $user);
		$eventRepository->create($event2, $site, $user);


		// Test before

		$event2 = $eventRepository->loadBySlug($site, $event2->getSlug());
		$this->assertFalse($event2->getIsDeleted());
		$this->assertNull($event2->getIsDuplicateOfId());


		// Mark
		TimeSource::mock(2014,5,1,8,0,0);
		$eventRepository->markDuplicate($event2, $event1, $user);


		// Test Duplicate

		$event2 = $eventRepository->loadBySlug($site, $event2->getSlug());
		$this->assertTrue($event2->getIsDeleted());
		$this->assertEquals($event1->getId(), $event2->getIsDuplicateOfId());
		
	}


}




