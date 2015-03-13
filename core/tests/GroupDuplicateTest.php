<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\GroupRepository;
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
class GroupDuplicateTest extends \BaseAppWithDBTest {
	
	function test1() {
		\TimeSource::mock(2014,1,1,0,0,0);

		$user1 = new UserAccountModel();
		$user1->setEmail("test@jarofgreen.co.uk");
		$user1->setUsername("test");
		$user1->setPassword("password");

		$user2 = new UserAccountModel();
		$user2->setEmail("test2@jarofgreen.co.uk");
		$user2->setUsername("test2");
		$user2->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user1);
		$userRepo->create($user2);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user1, array(), $this->getSiteQuotaUsedForTesting());
		
		$group1 = new GroupModel();
		$group1->setTitle("test1");
		$group1->setDescription("test test");
		$group1->setUrl("http://www.group.com");

		$group2 = new GroupModel();
		$group2->setTitle("test this looks similar");
		$group2->setDescription("test test");
		$group2->setUrl("http://www.group.com");

		$groupRepo = new GroupRepository();

		\TimeSource::mock(2014,1,1,1,0,0);
		$groupRepo->create($group1, $site, $user1);
		$groupRepo->create($group2, $site, $user2);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user1, $group2);

		$uwgr = new UserWatchesGroupRepository();


		// Test before

		$erb = new \repositories\builders\EventRepositoryBuilder();
		$erb->setGroup($group1);
		$this->assertEquals(0, count($erb->fetchAll()));

		$this->assertNull($uwgr->loadByUserAndGroup($user2, $group1));

		$group2 = $groupRepo->loadById($group2->getId());
		$this->assertFalse($group2->getIsDeleted());
		$this->assertNull($group2->getIsDuplicateOfId());


		// Mark
		\TimeSource::mock(2014,1,1,2,0,0);
		$groupRepo->markDuplicate($group2, $group1, $user1);


		// Test Duplicate

		$erb = new \repositories\builders\EventRepositoryBuilder();
		$erb->setGroup($group1);
		$this->assertEquals(1, count($erb->fetchAll()));

		$uwg = $uwgr->loadByUserAndGroup($user2, $group1);
		$this->assertNotNull($uwg);

		$group2 = $groupRepo->loadById($group2->getId());
		$this->assertTrue($group2->getIsDeleted());
		$this->assertEquals($group1->getId(), $group2->getIsDuplicateOfId());



	}


	
}




