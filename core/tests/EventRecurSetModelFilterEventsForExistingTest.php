<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use models\EventRecurSetModel;
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
class EventRecurSetModelFilterEventsForExistingTest extends \PHPUnit_Framework_TestCase {

	public function mktime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
		$dt = new \DateTime('', new \DateTimeZone('UTC'));
		$dt->setTime($hour, $minute, $second);
		$dt->setDate($year, $month, $day);
		return $dt;
	}
	
	function testExists1() {
		$DB = getNewTestDB();
		
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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$event1 = new EventModel();
		$event1->setStartAt($this->mktime(2013,8,1,19,0,0));
		$event1->setEndAt($this->mktime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event1, $site, $user, $group);
		
		$event2 = new EventModel();
		$event2->setStartAt($this->mktime(2013,8,2,19,0,0));
		$event2->setEndAt($this->mktime(2013,8,2,21,0,0));

		$eventRepository->create($event2, $site, $user, $group);

		$eventProposed = new EventModel();
		$eventProposed->setStartAt($this->mktime(2013,8,2,19,0,0));
		$eventProposed->setEndAt($this->mktime(2013,8,2,21,0,0));
		
		$ersm = new EventRecurSetModel();
		$event1 = $eventRepository->loadBySlug($site, $event1->getSlug());
		$events = $ersm->filterEventsForExisting($event1, array($eventProposed));
		
		$this->assertEquals(0, count($events));
		
	}
	
	function testExistsInDifferentGroup1() {
		$DB = getNewTestDB();
		
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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$event1 = new EventModel();
		$event1->setStartAt($this->mktime(2013,8,1,19,0,0));
		$event1->setEndAt($this->mktime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event1, $site, $user, $group);
		
		// this event is not in the same group as event1 so it won't count as a duplicate
		$event2 = new EventModel();
		$event2->setStartAt($this->mktime(2013,8,2,19,0,0));
		$event2->setEndAt($this->mktime(2013,8,2,21,0,0));

		$eventRepository->create($event2, $site, $user);

		$eventProposed = new EventModel();
		$eventProposed->setGroup($group);
		$eventProposed->setStartAt($this->mktime(2013,8,2,19,0,0));
		$eventProposed->setEndAt($this->mktime(2013,8,2,21,0,0));
		
		$ersm = new EventRecurSetModel();
		$events = $ersm->filterEventsForExisting($event1, array($eventProposed));
		
		$this->assertEquals(1, count($events));
		
	}
	
	function testNotExists1() {
		$DB = getNewTestDB();
		
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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$event1 = new EventModel();
		$event1->setGroup($group);
		$event1->setStartAt($this->mktime(2013,8,1,19,0,0));
		$event1->setEndAt($this->mktime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event1, $site, $user);
		
		
		$eventProposed = new EventModel();
		$eventProposed->setGroup($group);
		$eventProposed->setStartAt($this->mktime(2013,8,2,19,0,0));
		$eventProposed->setEndAt($this->mktime(2013,8,2,21,0,0));
		
		
		$ersm = new EventRecurSetModel();
		$events = $ersm->filterEventsForExisting($event1, array($eventProposed));
		
		$this->assertEquals(1, count($events));
		
	}
	
}

