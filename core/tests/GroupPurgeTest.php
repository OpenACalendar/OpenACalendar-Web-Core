<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\UserWatchesGroupRepository;
use repositories\builders\GroupRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class GroupPurgeTest extends \PHPUnit_Framework_TestCase {
	
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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$ufgr = new UserWatchesGroupRepository();
		$ufgr->startUserWatchingGroupIdIfNotWatchedBefore($user, $group->getId());
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2013,8,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user, $group);

		## TEST
		$this->assertNotNull($groupRepo->loadById($group->getId()));
		
		$groupRB = new GroupRepositoryBuilder();
		$groupRB->setEvent($event);
		$groups = $groupRB->fetchAll();
		$this->assertEquals(1, count($groups));
		
		## PURGE!
		$groupRepo->purge($group);
		
		## TEST
		$this->assertNull($groupRepo->loadById($group->getId()));
		
		$groupRB = new GroupRepositoryBuilder();
		$groupRB->setEvent($event);
		$groups = $groupRB->fetchAll();
		$this->assertEquals(0, count($groups));
		
	}
	
	
}




