<?php

use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use org\openacalendar\curatedlists\models\CuratedListModel;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use org\openacalendar\curatedlists\repositories\CuratedListRepository;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListGroupTest extends \BaseAppWithDBTest {


	function test1() {

		TimeSource::mock(2014,5,1,7,0,0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

        // We are deliberately using the UserAccountRepository from this extension so we have tests to cover instantiating and using this class
        // It extends the core one so has all methods.
		$userRepo = new \org\openacalendar\curatedlists\repositories\UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");
		
		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);

		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");

		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user, $group);


		// Test Before
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

        $groupRepositoryBuilder = new \org\openacalendar\curatedlists\repositories\builders\GroupRepositoryBuilder($this->app);
        $groupRepositoryBuilder->setCuratedList($curatedList);
        $this->assertEquals(0, count($groupRepositoryBuilder->fetchAll()));

		// Add group to list
		TimeSource::mock(2014,5,1,8,0,0);
		$clRepo->addGroupToCuratedList($group, $curatedList, $user);


		// Test After

		// .... we don't ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertNull($eventWithInfo->getInCuratedListGroupId());
		$this->assertNull($eventWithInfo->getInCuratedListGroupSlug());
		$this->assertNull($eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());


		// .... we Do ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList, true);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertEquals($group->getId(), $eventWithInfo->getInCuratedListGroupId());
		$this->assertEquals($group->getSlug(), $eventWithInfo->getInCuratedListGroupSlug());
		$this->assertEquals($group->getTitle(), $eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->isEventInListViaGroup());
		$this->assertEquals($group->getId(), $curatedListWithInfo->getEventInListViaGroupId());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsGroup));

        $groupRepositoryBuilder = new \org\openacalendar\curatedlists\repositories\builders\GroupRepositoryBuilder($this->app);
        $groupRepositoryBuilder->setCuratedList($curatedList);
        $this->assertEquals(1, count($groupRepositoryBuilder->fetchAll()));

		// Remove group from list
		TimeSource::mock(2014,5,1,9,0,0);
		$clRepo->removeGroupFromCuratedList($group, $curatedList, $user);


		// Test After
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

        $groupRepositoryBuilder = new \org\openacalendar\curatedlists\repositories\builders\GroupRepositoryBuilder($this->app);
        $groupRepositoryBuilder->setCuratedList($curatedList);
        $this->assertEquals(0, count($groupRepositoryBuilder->fetchAll()));
	}

	function testEventInTwoGroupsOneAdded() {

		TimeSource::mock(2014,5,1,7,0,0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

        // We are deliberately using the UserAccountRepository from this extension so we have tests to cover instantiating and using this class
        // It extends the core one so has all methods.
		$userRepo = new \org\openacalendar\curatedlists\repositories\UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");

		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);

		$group1 = new GroupModel();
		$group1->setTitle("test");
		$group1->setDescription("test test");
		$group1->setUrl("http://www.group.com");


		$group2 = new GroupModel();
		$group2->setTitle("I don't need no stinking tests");
		$group2->setDescription("works first time");
		$group2->setUrl("http://www.soveryperfect.com");

		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group1, $site, $user);
		$groupRepo->create($group2, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user, $group1, array($group2));


		// Test Before
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		// Add group to list
		TimeSource::mock(2014,5,1,8,0,0);
		$clRepo->addGroupToCuratedList($group1, $curatedList, $user);


		// Test After

		// .... we don't ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertNull($eventWithInfo->getInCuratedListGroupId());
		$this->assertNull($eventWithInfo->getInCuratedListGroupSlug());
		$this->assertNull($eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());


		// .... we Do ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList, true);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertEquals($group1->getId(), $eventWithInfo->getInCuratedListGroupId());
		$this->assertEquals($group1->getSlug(), $eventWithInfo->getInCuratedListGroupSlug());
		$this->assertEquals($group1->getTitle(), $eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->getIsGroupInlist());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->isEventInListViaGroup());
		$this->assertEquals($group1->getId(), $curatedListWithInfo->getEventInListViaGroupId());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		// Remove group from list
		TimeSource::mock(2014,5,1,9,0,0);
		$clRepo->removeGroupFromCuratedList($group1, $curatedList, $user);


		// Test After
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));
	}

	function testEventInTwoGroupsBothAdded() {

		TimeSource::mock(2014,5,1,7,0,0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

        // We are deliberately using the UserAccountRepository from this extension so we have tests to cover instantiating and using this class
        // It extends the core one so has all methods.
		$userRepo = new \org\openacalendar\curatedlists\repositories\UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");

		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);

		$group1 = new GroupModel();
		$group1->setTitle("test");
		$group1->setDescription("test test");
		$group1->setUrl("http://www.group.com");


		$group2 = new GroupModel();
		$group2->setTitle("I don't need no stinking tests");
		$group2->setDescription("works first time");
		$group2->setUrl("http://www.soveryperfect.com");

		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group1, $site, $user);
		$groupRepo->create($group2, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user, $group1, array($group2));


		// Test Before
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));


		// Add group to list
		TimeSource::mock(2014,5,1,8,0,0);
		$clRepo->addGroupToCuratedList($group1, $curatedList, $user);
		$clRepo->addGroupToCuratedList($group2, $curatedList, $user);


		// Test After

		// .... we don't ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertNull($eventWithInfo->getInCuratedListGroupId());
		$this->assertNull($eventWithInfo->getInCuratedListGroupSlug());
		$this->assertNull($eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());


		// .... we Do ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList, true);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertEquals($group2->getId(), $eventWithInfo->getInCuratedListGroupId());
		$this->assertEquals($group2->getSlug(), $eventWithInfo->getInCuratedListGroupSlug());
		$this->assertEquals($group2->getTitle(), $eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->getIsGroupInlist());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->isEventInListViaGroup());
		$this->assertEquals($group2->getId(), $curatedListWithInfo->getEventInListViaGroupId());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsGroup));

		// Remove group from list
		TimeSource::mock(2014,5,1,9,0,0);
		$clRepo->removeGroupFromCuratedList($group1, $curatedList, $user);
		$clRepo->removeGroupFromCuratedList($group2, $curatedList, $user);


		// Test After
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

	}


	function testEventInTwoGroupsAddedDirectlyThenOneGroupAdded() {

		TimeSource::mock(2014,5,1,7,0,0);

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

        // We are deliberately using the UserAccountRepository from this extension so we have tests to cover instantiating and using this class
        // It extends the core one so has all methods.
		$userRepo = new \org\openacalendar\curatedlists\repositories\UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");

		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);

		$group1 = new GroupModel();
		$group1->setTitle("test");
		$group1->setDescription("test test");
		$group1->setUrl("http://www.group.com");


		$group2 = new GroupModel();
		$group2->setTitle("I don't need no stinking tests");
		$group2->setDescription("works first time");
		$group2->setUrl("http://www.soveryperfect.com");

		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group1, $site, $user);
		$groupRepo->create($group2, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user, $group1, array($group2));


		// Test Before
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		// Add group to list
		TimeSource::mock(2014,5,1,8,0,0);
		$clRepo->addEventtoCuratedList($event, $curatedList, $user);
		$clRepo->addGroupToCuratedList($group1, $curatedList, $user);


		// Test After

		// .... we don't ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertNull($eventWithInfo->getInCuratedListGroupId());
		$this->assertNull($eventWithInfo->getInCuratedListGroupSlug());
		$this->assertNull($eventWithInfo->getInCuratedListGroupTitle());
		$this->assertFalse($eventWithInfo->getIsEventInCuratedList());


		// .... we Do ask for extra info
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList, true);
		$events = $eventRepositoryBuilder->fetchAll();
		$this->assertEquals(1, count($events));
		$eventWithInfo = $events[0];
		$this->assertEquals($group1->getId(), $eventWithInfo->getInCuratedListGroupId());
		$this->assertEquals($group1->getSlug(), $eventWithInfo->getInCuratedListGroupSlug());
		$this->assertEquals($group1->getTitle(), $eventWithInfo->getInCuratedListGroupTitle());
		$this->assertTrue($eventWithInfo->getIsEventInCuratedList());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->getIsGroupInlist());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(true, $curatedListWithInfo->isEventInListViaGroup());
		$this->assertEquals($group1->getId(), $curatedListWithInfo->getEventInListViaGroupId());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		// Remove group from list
		TimeSource::mock(2014,5,1,9,0,0);
		$clRepo->removeGroupFromCuratedList($group1, $curatedList, $user);
		$clRepo->removeEventFromCuratedList($event, $curatedList, $user);


		// Test After
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder($this->app);
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setGroupInformation($group1);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->getIsGroupInlist());



		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setEventInformation($event);
		$curatedListsWithInfo = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(1, count($curatedListsWithInfo));
		$curatedListWithInfo = $curatedListsWithInfo[0];
		$this->assertEquals(false, $curatedListWithInfo->isEventInListViaGroup());


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsEvent($event);
		$curatedListsContainsEvent = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsEvent));


		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group1);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder($this->app);
		$curatedListRepoBuilder->setContainsGroup($group2);
		$curatedListsContainsGroup = $curatedListRepoBuilder->fetchAll();
		$this->assertEquals(0, count($curatedListsContainsGroup));
	}

}


