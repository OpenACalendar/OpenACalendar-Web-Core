<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\builders\GroupRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class EventInGroupCreateTest extends \PHPUnit_Framework_TestCase {
	
	
	public function mktime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
		$dt = new \DateTime('', new \DateTimeZone('UTC'));
		$dt->setTime($hour, $minute, $second);
		$dt->setDate($year, $month, $day);
		return $dt;
	}
	
	function test1() {
		$DB = getNewTestDB();

		TimeSource::mock(2013,7,1,7,0,0);
		
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
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt($this->mktime(2013,8,1,19,0,0));
		$event->setEndAt($this->mktime(2013,8,1,21,0,0));
		$event->setGroup($group);

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);
		
		$this->checkEventInTest1($eventRepository->loadBySlug($site, $event->getSlug()));
		
	}
	
	protected function checkEventInTest1(EventModel $event) {
		$this->assertEquals("test test", $event->getDescription());
		$this->assertEquals("test", $event->getSummary());
		// TODO start end
	}
	
	function testMultiple() {
		$DB = getNewTestDB();

		TimeSource::mock(2013,7,1,7,0,0);
		
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
		
		$group1 = new GroupModel();
		$group1->setTitle("test");
		$group1->setDescription("test test");
		$group1->setUrl("http://www.group.com");
		
		$group2 = new GroupModel();
		$group2->setTitle("cat");
		$group2->setDescription("cat cat");
		$group2->setUrl("http://www.cat.com");
		
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group1, $site, $user);
		$groupRepo->create($group2, $site, $user);
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt($this->mktime(2013,8,1,19,0,0));
		$event->setEndAt($this->mktime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user, $group1, array($group1, $group2));

		// Check groups
		$groupRB = new GroupRepositoryBuilder();
		$groupRB->setEvent($event);
		$groups = $groupRB->fetchAll();
		$this->assertEquals(2, count($groups));
	}
}




