<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventCreateTest extends \PHPUnit_Framework_TestCase {
	
	
	public function mktime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0, $timeZone='Europe/London') {
		$dt = new \DateTime('', new \DateTimeZone($timeZone));
		$dt->setTime($hour, $minute, $second);
		$dt->setDate($year, $month, $day);
		return $dt;
	}
	
	function testSummerTime() {
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
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt($this->mktime(2014,5,10,19,0,0,'Europe/London'));
		$event->setEndAt($this->mktime(2014,5,10,21,0,0,'Europe/London'));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		
		$this->assertEquals("test test", $event->getDescription());
		$this->assertEquals("test", $event->getSummary());
		$this->assertEquals("http://www.info.com", $event->getUrl());
		$this->assertEquals("http://www.tickets.com", $event->getTicketUrl());
				
		$startAtShouldBe = $this->mktime(2014,5,10,18,0,0,'UTC'); // Not summer time so London is +1 UTC!
		$startAtIs = clone $event->getStartAt();
		$startAtIs->setTimezone(new \DateTimeZone('UTC'));
		$this->assertEquals($startAtShouldBe->format("c"), $startAtIs->format("c"));
		
		
	}
	
	function testWinterTime() {
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
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt($this->mktime(2014,11,10,19,0,0,'Europe/London'));
		$event->setEndAt($this->mktime(2014,11,10,21,0,0,'Europe/London'));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadBySlug($site, $event->getSlug());

		
		$this->assertEquals("test test", $event->getDescription());
		$this->assertEquals("test", $event->getSummary());
		
		$startAtShouldBe = $this->mktime(2014,11,10,19,0,0,'UTC');
		$startAtIs = clone $event->getStartAt();
		$startAtIs->setTimezone(new \DateTimeZone('UTC'));
		$this->assertEquals($startAtShouldBe->format("c"), $startAtIs->format("c"));
		
		
	}
	
}




