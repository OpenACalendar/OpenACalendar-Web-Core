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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupPromptTest extends \PHPUnit_Framework_TestCase {
	
	function setUp() {
		global $CONFIG;
	}


	// TODO test1 from UserWatchesSitePromptTest

	// TODO test2 from UserWatchesSitePromptTest

	// TODO test3 from UserWatchesSitePromptTest

	// TODO test4 from UserWatchesSitePromptTest

	// TODO test5 from UserWatchesSitePromptTest

	/**
	 * One event, 31 days from now, then 30 days, then 29 days, etc, only 1 email sent
	 * @global type $CONFIG
	 */
	function test6() {
		global $CONFIG;
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);
		
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
		$group->setTitle("Group");

		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 30, 9);
		$start->setTime(9,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
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
			\TimeSource::mock(2013, $day, 8, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);
		}

		#Email sent!
		\TimeSource::mock(2013, 30, 8, 1, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertTrue($data['moreEventsNeeded']);
		$userWatchesGroupRepo->markPromptEmailSent($userWatchesGroup, \TimeSource::getDateTime());
		
		#After email sent
		\TimeSource::mock(2013, 31, 8, 1, 0, 0);
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
		$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
		$this->assertFalse($data['moreEventsNeeded']);
		
		for ($day = 1; $day <= 30; $day++) {
			\TimeSource::mock(2013, $day, 9, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 31; $day++) {
			\TimeSource::mock(2013, $day, 10, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 30; $day++) {
			\TimeSource::mock(2013, $day, 11, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 31; $day++) {
			\TimeSource::mock(2013, $day, 12, 1, 0, 0);
			$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $group);
			$data = $userWatchesGroup->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
	}

	
	
}

