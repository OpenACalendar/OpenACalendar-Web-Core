<?php

use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use models\UserWatchesSiteModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\UserWatchesSiteRepository;
use repositories\EventRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSitePromptTest extends \BaseAppWithDBTest {

	
	/**
	 * No events. Don't send email.
	 * @global type $CONFIG
	 */
	function test1() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;

	
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

		$eventRepo = new EventRepository();
		
		// User will watch site automatically in site->create()
		
		# Test
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertFalse($data['moreEventsNeeded']);
		
	}
	
	/**
	 * One event, months ago. Def send email.
	 * @global type $CONFIG
	 */
	function test2() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;

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
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 5, 1);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
		$end->setDate(2013, 5, 1);
		$end->setTime(1,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);
		
		// User will watch site automatically in site->create()
				
		# Test
		\TimeSource::mock(2013, 9, 1, 0, 0, 0);
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertTrue($data['moreEventsNeeded']);

		
	}
	
	/**
	 * One event, months in future. Don't Send email.
	 * @global type $CONFIG
	 */
	function test3() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;

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
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 12, 1);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
		$end->setDate(2013, 12, 1);
		$end->setTime(1,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);
		
		// User will watch site automatically in site->create()
				
		# Test
		\TimeSource::mock(2013, 6, 1, 0, 0, 0);
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertFalse($data['moreEventsNeeded']);

		
	}
	
	/**
	 * One event, week from now, send email.
	 * @global type $CONFIG
	 */
	function test4() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;

	
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
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 6, 7);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
		$end->setDate(2013, 6, 7);
		$end->setTime(1,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);
		
		// User will watch site automatically in site->create()
				
		# Test
		\TimeSource::mock(2013, 6, 1, 0, 0, 0);
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertTrue($data['moreEventsNeeded']);

		
	}
	
	/**
	 * One event, week from now, but email sent 29 days ago. Don't send email.
	 * This tests $CONFIG->userWatchesPromptEmailSafeGapDays works.
	 * @global type $CONFIG
	 */
	function test4a() {
		
		global $CONFIG;
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);

	
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
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 6, 7);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
		$end->setDate(2013, 6, 7);
		$end->setTime(1,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);
		
		// User will watch site automatically in site->create()
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		
		\TimeSource::mock(2013, 5, 2, 0, 0, 0);
		$userWatchesSiteRepo->markPromptEmailSent($userWatchesSite, \TimeSource::getDateTime());
		
		# Test
		\TimeSource::mock(2013, 6, 1, 0, 0, 0);
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertFalse($data['moreEventsNeeded']);

		
	}
	
	/**
	 * One event, week from now, but emailed yesterday, dont send email.
	 * @global type $CONFIG
	 */
	function test5() {
		global $CONFIG;
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);

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
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 6, 7);
		$start->setTime(0,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
		$end->setDate(2013, 6, 7);
		$end->setTime(1,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);
		
		// User will watch site automatically in site->create()
		
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		
		\TimeSource::mock(2013, 6, 1, 0, 0, 0);
		$userWatchesSiteRepo->markPromptEmailSent($userWatchesSite, \TimeSource::getDateTime());

		# Test
		\TimeSource::mock(2013, 6, 2, 0, 0, 0);
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertFalse($data['moreEventsNeeded']);

		
	}
	
	/**
	 * One event, 31 days from now, then 30 days, then 29 days, etc, only 1 email sent
	 * @global type $CONFIG
	 */
	function test6() {
		global $CONFIG;
		$CONFIG->userWatchesPromptEmailSafeGapDays = 30;
		
		\TimeSource::mock(2013, 1, 1, 0, 0, 0);

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
		$start = \TimeSource::getDateTime();
		$start->setDate(2013, 30, 9);
		$start->setTime(9,0,0);
		$event->setStartAt($start);
		$end = \TimeSource::getDateTime();
		$end->setDate(2013, 30, 9);
		$end->setTime(12,0,0);
		$event->setEndAt($end);
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);
		
		// User will watch site automatically in site->create()
		
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		
		#Before email sent!
		for ($day = 1; $day <= 29; $day++) {
			\TimeSource::mock(2013, $day, 8, 1, 0, 0);
			$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
			$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
			$this->assertFalse($data['moreEventsNeeded']);
		}

		#Email sent!
		\TimeSource::mock(2013, 30, 8, 1, 0, 0);
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertTrue($data['moreEventsNeeded']);
		$userWatchesSiteRepo->markPromptEmailSent($userWatchesSite, \TimeSource::getDateTime());
		
		#After email sent
		\TimeSource::mock(2013, 31, 8, 1, 0, 0);
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
		$this->assertFalse($data['moreEventsNeeded']);
		
		for ($day = 1; $day <= 30; $day++) {
			\TimeSource::mock(2013, $day, 9, 1, 0, 0);
			$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
			$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 31; $day++) {
			\TimeSource::mock(2013, $day, 10, 1, 0, 0);
			$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
			$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 30; $day++) {
			\TimeSource::mock(2013, $day, 11, 1, 0, 0);
			$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
			$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
		for ($day = 1; $day <= 31; $day++) {
			\TimeSource::mock(2013, $day, 12, 1, 0, 0);
			$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
			$data = $userWatchesSite->getPromptEmailData($site, $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId()));
			$this->assertFalse($data['moreEventsNeeded']);	
		}
		
	}

	
	
}

