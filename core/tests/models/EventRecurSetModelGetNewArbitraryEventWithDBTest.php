<?php

namespace tests\models;

use models\EventModel;
use models\EventRecurSetModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\EventRecurSetRepository;
use repositories\EventRepository;
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
class EventRecurSetModelGetNewArbitraryEventWithDBTest extends \BaseAppWithDBTest {


	function test1() {

		\TimeSource::mock(2015,5,1,7,0,0);

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
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setTimezone('Europe/London');
		$start = new \DateTime("", new \DateTimeZone('Europe/London'));
		$start->setDate(2015,5,10);
		$start->setTime(19,0,0);
		$event->setStartAt($start);
		$end = new \DateTime("", new \DateTimeZone('Europe/London'));
		$end->setDate(2015,5,10);
		$end->setTime(21,0,0);
		$event->setEndAt($end);
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadBySlug($site, $event->getSlug());


		$eventRecurSetRepository = new EventRecurSetRepository();
		$eventRecurSet = $eventRecurSetRepository->getForEvent($event);
		$eventRecurSet->setTimeZoneName($event->getTimezone());

		$newStart = new \DateTime("", new \DateTimeZone($event->getTimezone()));
		$newStart->setDate(2015,6,1);

		$newEvent = $eventRecurSet->getNewEventOnArbitraryDate($event, $newStart);

		// What we are really testing here is start and end times set correctly
		$this->assertEquals("2015-06-01T18:00:00+00:00", $newEvent->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-06-01T20:00:00+00:00", $newEvent->getEndAtInUTC()->format("c"));

		$this->assertEquals("2015-06-01T19:00:00+01:00", $newEvent->getStartAtInTimezone()->format("c"));
		$this->assertEquals("2015-06-01T21:00:00+01:00", $newEvent->getEndAtInTimezone()->format("c"));



	}


	function testAcrossBST1() {

		\TimeSource::mock(2015,5,1,7,0,0);

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
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setTimezone('Europe/London');
		$start = new \DateTime("", new \DateTimeZone('Europe/London'));
		$start->setDate(2015,5,10);
		$start->setTime(19,0,0);
		$event->setStartAt($start);
		$end = new \DateTime("", new \DateTimeZone('Europe/London'));
		$end->setDate(2015,5,10);
		$end->setTime(21,0,0);
		$event->setEndAt($end);
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadBySlug($site, $event->getSlug());


		$eventRecurSetRepository = new EventRecurSetRepository();
		$eventRecurSet = $eventRecurSetRepository->getForEvent($event);
		$eventRecurSet->setTimeZoneName($event->getTimezone());


		$newStart = new \DateTime();
		$newStart->setDate(2015,11,1);

		$newEvent = $eventRecurSet->getNewEventOnArbitraryDate($event, $newStart);

		// What we are really testing here is start and end times set correctly
		$this->assertEquals("2015-11-01T19:00:00+00:00", $newEvent->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-11-01T21:00:00+00:00", $newEvent->getEndAtInUTC()->format("c"));

		$this->assertEquals("2015-11-01T19:00:00+00:00", $newEvent->getStartAtInTimezone()->format("c"));
		$this->assertEquals("2015-11-01T21:00:00+00:00", $newEvent->getEndAtInTimezone()->format("c"));



	}




}

