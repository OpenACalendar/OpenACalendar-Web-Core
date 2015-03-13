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
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class GroupPurgeTest extends \BaseAppWithDBTest {
	
	function testMultiple() {

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
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");

		$groupDupe = new GroupModel();
		$groupDupe->setTitle("test DUPE");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		$groupRepo->create($groupDupe, $site, $user);
		TimeSource::mock(2013,7,1,7,1,0);
		$groupRepo->markDuplicate($groupDupe, $group);

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




