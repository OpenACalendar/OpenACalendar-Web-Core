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
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelGetNewMontlyEventsTest extends \BaseAppWithDBTest
{


	function testEventAndNotEvent1() {

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

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2013,8,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");


		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		## Test event not in group
		$grb = new GroupRepositoryBuilder();
		$grb->setEvent($event);
		$this->assertEquals(0, count($grb->fetchAll()));

		$grb = new GroupRepositoryBuilder();
		$grb->setNotEvent($event);
		$this->assertEquals(1, count($grb->fetchAll()));

		## Add event to group, test
		$groupRepo->addEventToGroup($event, $group, $user);

		$grb = new GroupRepositoryBuilder();
		$grb->setEvent($event);
		$this->assertEquals(1, count($grb->fetchAll()));

		$grb = new GroupRepositoryBuilder();
		$grb->setNotEvent($event);
		$this->assertEquals(0, count($grb->fetchAll()));

		## remove event from group
		$groupRepo->removeEventFromGroup($event, $group, $user);

		$grb = new GroupRepositoryBuilder();
		$grb->setEvent($event);
		$this->assertEquals(0, count($grb->fetchAll()));

		$grb = new GroupRepositoryBuilder();
		$grb->setNotEvent($event);
		$this->assertEquals(1, count($grb->fetchAll()));
	}



}
