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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupRepositoryTest extends \BaseAppWithDBTest {
	
	function testIsGroupRunningOutOfFutureEvents() {

		$this->app['timesource']->mock(2014,1,1,1,1,1);
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		$site->setPromptEmailsDaysInAdvance(28);
		
		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,4,1,19,0,0,'Europe/London'));
		$event->setEndAt(getUTCDateTime(2014,4,1,21,0,0,'Europe/London'));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user, $group);
		
		### TEST
		$this->app['timesource']->mock(2014,2,1,1,1,1);
		$this->assertEquals(0, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,2,15,1,1,1);
		$this->assertEquals(0, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,1,1,1,1);
		$this->assertEquals(0, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,2,1,1,1);
		$this->assertEquals(0, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,3,1,1,1);
		$this->assertEquals(0, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,4,1,1,1);
		$this->assertEquals(0, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,5,1,1,1);
		$this->assertEquals(1, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,6,1,1,1);
		$this->assertEquals(1, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,7,1,1,1);
		$this->assertEquals(1, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,3,20,1,1,1);
		$this->assertEquals(1, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,4,1,1,1,1);
		$this->assertEquals(1, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,4,15,1,1,1);
		$this->assertEquals(2, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		### TEST
		$this->app['timesource']->mock(2014,5,1,1,1,1);
		$this->assertEquals(2, $groupRepo->isGroupRunningOutOfFutureEvents($group, $site));
		
		
		
	}
	
	
}




