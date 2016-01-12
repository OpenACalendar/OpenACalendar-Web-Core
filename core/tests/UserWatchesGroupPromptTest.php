<?php

use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use models\UserWatchesGroupModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesGroupRepository;
use repositories\EventRepository;
use repositories\GroupRepository;
use repositories\builders\UserWatchesGroupRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupPromptTest extends \BaseAppWithDBTest {


	/**
	 * No events. Don't send email.
	 */
	function test1() {

		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);
		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;

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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$eventRepo = new EventRepository();

		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);


		# Test
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertFalse($data['moreEventsNeeded']);

	}

	/**
	 * One event, months ago. Def send email.
	 */
	function test2() {

		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);
		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;


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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = $this->app['timesource']->getDateTime();
		$start->setDate(2013, 5, 1);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = $this->app['timesource']->getDateTime();
		$end->setDate(2013, 5, 1);
		$end->setTime(1,0,0);
		$event->setEndAt($end);

		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user, $group);

		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);

		# Test
		$this->app['timesource']->mock(2013, 9, 1, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertTrue($data['moreEventsNeeded']);


	}

	/**
	 * One event, months in future. Don't Send email.
	 */
	function test3() {

		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);
		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;


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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = $this->app['timesource']->getDateTime();
		$start->setDate(2013, 12, 1);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = $this->app['timesource']->getDateTime();
		$end->setDate(2013, 12, 1);
		$end->setTime(1,0,0);
		$event->setEndAt($end);

		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user, $group);


		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);


		# Test
		$this->app['timesource']->mock(2013, 6, 1, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertFalse($data['moreEventsNeeded']);


	}

	/**
	 * One event, week from now, send email.
	 */
	function test4() {

		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);
		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;


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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = $this->app['timesource']->getDateTime();
		$start->setDate(2013, 6, 7);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = $this->app['timesource']->getDateTime();
		$end->setDate(2013, 6, 7);
		$end->setTime(1,0,0);
		$event->setEndAt($end);

		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user, $group);


		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);


		# Test
		$this->app['timesource']->mock(2013, 6, 1, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertTrue($data['moreEventsNeeded']);


	}

	/**
	 * One event, week from now, but email sent 29 days ago. Don't send email.
	 * This tests $this->app['config']->userWatchesPromptEmailSafeGapDays works.
	 */
	function test4a() {

		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;

		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);


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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = $this->app['timesource']->getDateTime();
		$start->setDate(2013, 6, 7);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = $this->app['timesource']->getDateTime();
		$end->setDate(2013, 6, 7);
		$end->setTime(1,0,0);
		$event->setEndAt($end);

		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user, $group);

		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);

		$this->app['timesource']->mock(2013, 5, 2, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$userWatchesGroupRepo->markPromptEmailSent($userWatchesGroup, $this->app['timesource']->getDateTime());

		# Test
		$this->app['timesource']->mock(2013, 6, 1, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertFalse($data['moreEventsNeeded']);


	}

	/**
	 * One event, week from now, but emailed yesterday, dont send email.
	 */
	function test5() {
		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;

		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);


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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = $this->app['timesource']->getDateTime();
		$start->setDate(2013, 6, 7);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = $this->app['timesource']->getDateTime();
		$end->setDate(2013, 6, 7);
		$end->setTime(1,0,0);
		$event->setEndAt($end);

		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user, $group);

		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);


		$this->app['timesource']->mock(2013, 6, 1, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$userWatchesGroupRepo->markPromptEmailSent($userWatchesGroup, $this->app['timesource']->getDateTime());

		# Test
		$this->app['timesource']->mock(2013, 6, 2, 0, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertFalse($data['moreEventsNeeded']);


	}

	/**
	 * One event, 31 days from now, then 30 days, then 29 days, etc, only 1 email sent
	 */
	function test6() {
		$this->app['config']->userWatchesPromptEmailSafeGapDays = 30;
		
		$this->app['timesource']->mock(2013, 1, 1, 0, 0, 0);
		

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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = $this->app['timesource']->getDateTime();
		$start->setDate(2013, 30, 9);
		$start->setTime(9,0,0);
		$event->setStartAt($start);
		$end = $this->app['timesource']->getDateTime();
		$end->setDate(2013, 30, 9);
		$end->setTime(12,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user, $group);
		
		// User will watch site automatically in site->create()
		// We don't want that, we want the group instead
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroupRepo->startUserWatchingGroup($user, $group);
		
		#Before email sent!
		for ($day = 1; $day <= 29; $day++) {
			$this->app['timesource']->mock(2013, $day, 8, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);
		}

		#Email sent!
		$this->app['timesource']->mock(2013, 30, 8, 1, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertTrue($data['moreEventsNeeded']);
		$userWatchesGroupRepo->markPromptEmailSent($userWatchesGroup, $this->app['timesource']->getDateTime());
		
		#After email sent
		$this->app['timesource']->mock(2013, 31, 8, 1, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertFalse($data['moreEventsNeeded']);
		
		for ($day = 1; $day <= 30; $day++) {
			$this->app['timesource']->mock(2013, $day, 9, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 31; $day++) {
			$this->app['timesource']->mock(2013, $day, 10, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 30; $day++) {
			$this->app['timesource']->mock(2013, $day, 11, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 31; $day++) {
			$this->app['timesource']->mock(2013, $day, 12, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
	}

	
	
}

