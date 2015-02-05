<?php

use models\EventModel;
use models\GroupModel;
use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use repositories\EventRepository;
use repositories\GroupRepository;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;
use repositories\builders\EventRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */




class EventRepositoryBuilderUserTest  extends \PHPUnit_Framework_TestCase {



	function testUserAttendingEventNoWatches() {

		$DB = getNewTestDB();
		addCountriesToTestDB();

		$countryRepo = new CountryRepository();
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$groupRepo = new GroupRepository();
		$eventRepository = new EventRepository();
		$userWatchesGroupRepo = new \repositories\UserWatchesGroupRepository();
		$userAtEventRepo = new \repositories\UserAtEventRepository();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo->create($user);

		$userAttending = new UserAccountModel();
		$userAttending->setEmail("test2@jarofgreen.co.uk");
		$userAttending->setUsername("test2");
		$userAttending->setPassword("password1");

		$userRepo->create($userAttending);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), getSiteQuotaUsedForTesting());

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,11,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,11,10,21,0,0));

		$eventRepository->create($event, $site, $user);

		$userAtEvent = $userAtEventRepo->loadByUserAndEventOrInstanciate($userAttending, $event);
		$userAtEvent->setIsPlanAttending(true);
		$userAtEvent->setIsPlanPublic(false);
		$userAtEventRepo->save($userAtEvent);

		// test

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userAttending, false, true, true, true);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userAttending, false, false, true, true);
		$events = $erb->fetchAll();
		$this->assertEquals(0, count($events));

	}


	function testUserAttendingEventPublicallyNoWatches() {

		$DB = getNewTestDB();
		addCountriesToTestDB();

		$countryRepo = new CountryRepository();
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$groupRepo = new GroupRepository();
		$eventRepository = new EventRepository();
		$userWatchesGroupRepo = new \repositories\UserWatchesGroupRepository();
		$userAtEventRepo = new \repositories\UserAtEventRepository();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo->create($user);

		$userAttending = new UserAccountModel();
		$userAttending->setEmail("test2@jarofgreen.co.uk");
		$userAttending->setUsername("test2");
		$userAttending->setPassword("password1");

		$userRepo->create($userAttending);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), getSiteQuotaUsedForTesting());

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,11,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,11,10,21,0,0));

		$eventRepository->create($event, $site, $user);

		$userAtEvent = $userAtEventRepo->loadByUserAndEventOrInstanciate($userAttending, $event);
		$userAtEvent->setIsPlanAttending(true);
		$userAtEvent->setIsPlanPublic(true);
		$userAtEventRepo->save($userAtEvent);

		// test

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userAttending, false, true, true, true);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userAttending, false, false, true, true);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));

	}


	function testUserWatchingGroupsWithAnEventInTwoGroups() {

		$DB = getNewTestDB();
		addCountriesToTestDB();

		$countryRepo = new CountryRepository();
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$groupRepo = new GroupRepository();
		$eventRepository = new EventRepository();
		$userWatchesGroupRepo = new \repositories\UserWatchesGroupRepository();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo->create($user);

		$userWatchesMain = new UserAccountModel();
		$userWatchesMain->setEmail("test1@jarofgreen.co.uk");
		$userWatchesMain->setUsername("test1");
		$userWatchesMain->setPassword("password1");

		$userRepo->create($userWatchesMain);

		$userWatchesOther = new UserAccountModel();
		$userWatchesOther->setEmail("test2@jarofgreen.co.uk");
		$userWatchesOther->setUsername("test2");
		$userWatchesOther->setPassword("password1");

		$userRepo->create($userWatchesOther);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), getSiteQuotaUsedForTesting());

		$groupMain = new GroupModel();
		$groupMain->setTitle("test");

		$groupRepo->create($groupMain, $site, $user);

		$groupOther = new GroupModel();
		$groupOther->setTitle("test");

		$groupRepo->create($groupOther, $site, $user);


		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,11,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,11,10,21,0,0));

		$eventRepository->create($event, $site, $user, $groupMain, array($groupOther));

		// test watching main group gets event
		$userWatchesGroupRepo->startUserWatchingGroup($userWatchesMain, $groupMain);

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userWatchesMain, false, true, true, true);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userWatchesMain, false, true, true, false);
		$events = $erb->fetchAll();
		$this->assertEquals(0, count($events));

		// test watching other group gets event
		$userWatchesGroupRepo->startUserWatchingGroup($userWatchesOther, $groupOther);

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userWatchesOther, false, true, true, true);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));

		$erb = new EventRepositoryBuilder();
		$erb->setUserAccount($userWatchesOther, false, true, true, false);
		$events = $erb->fetchAll();
		$this->assertEquals(0, count($events));

	}
	
}
