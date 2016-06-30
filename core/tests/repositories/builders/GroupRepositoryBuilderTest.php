<?php

namespace tests\repositories\builders;


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\builders\GroupRepositoryBuilder;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupRepositoryBuilderTest extends \BaseAppWithDBTest
{


	function testEventAndNotEvent1() {

		$this->app['timesource']->mock(2013,7,1,7,0,0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2013,8,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user);

		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");


		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);

		## Test event not in group
		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setEvent($event);
		$this->assertEquals(0, count($grb->fetchAll()));

		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setNotEvent($event);
		$this->assertEquals(1, count($grb->fetchAll()));

		## Add event to group, test
		$groupRepo->addEventToGroup($event, $group, $user);

		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setEvent($event);
		$this->assertEquals(1, count($grb->fetchAll()));

		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setNotEvent($event);
		$this->assertEquals(0, count($grb->fetchAll()));

		## remove event from group
		$groupRepo->removeEventFromGroup($event, $group, $user);

		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setEvent($event);
		$this->assertEquals(0, count($grb->fetchAll()));

		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setNotEvent($event);
		$this->assertEquals(1, count($grb->fetchAll()));
	}

	function testTitleSearch() {

		$this->app['timesource']->mock(2013,7,1,7,0,0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");


		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);

		## Test found
		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setTitleSearch('Tes');
		$this->assertEquals(1, count($grb->fetchAll()));

		## Test Not Found
		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setTitleSearch('CATS');
		$this->assertEquals(0, count($grb->fetchAll()));

	}


}
