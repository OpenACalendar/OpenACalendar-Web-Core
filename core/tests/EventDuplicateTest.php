<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventDuplicateTest extends \PHPUnit_Framework_TestCase {


	
	function test1() {
		$DB = getNewTestDB();

		TimeSource::mock(2014,5,1,7,0,0);
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$user1 = new UserAccountModel();
		$user1->setEmail("test1@jarofgreen.co.uk");
		$user1->setUsername("test1");
		$user1->setPassword("password");

		$user2 = new UserAccountModel();
		$user2->setEmail("test2@jarofgreen.co.uk");
		$user2->setUsername("test2");
		$user2->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($user1);
		$userRepo->create($user2);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		$event1 = new EventModel();
		$event1->setSummary("test");
		$event1->setDescription("test test");
		$event1->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event1->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event1->setUrl("http://www.info.com");
		$event1->setTicketUrl("http://www.tickets.com");

		$event2 = new EventModel();
		$event2->setSummary("test this looks similar");
		$event2->setDescription("test test");
		$event2->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event2->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event2->setUrl("http://www.info.com");
		$event2->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event1, $site, $user);
		$eventRepository->create($event2, $site, $user);

		$userAtEventRepo = new \repositories\UserAtEventRepository();

		$user1AtEvent1 = $userAtEventRepo->loadByUserAndEventOrInstanciate($user1, $event1);
		$user1AtEvent1->setIsPlanAttending(true);
		$user1AtEvent1->setIsPlanPublic(true);
		$userAtEventRepo->save($user1AtEvent1);

		$user1AtEvent2 = $userAtEventRepo->loadByUserAndEventOrInstanciate($user1, $event2);
		$user1AtEvent2->setIsPlanMaybeAttending(true);
		$userAtEventRepo->save($user1AtEvent2);

		$user2AtEvent2 = $userAtEventRepo->loadByUserAndEventOrInstanciate($user2, $event2);
		$user2AtEvent2->setIsPlanMaybeAttending(true);
		$userAtEventRepo->save($user2AtEvent2);




		//=============================================== Test before

		$event2 = $eventRepository->loadBySlug($site, $event2->getSlug());
		$this->assertFalse($event2->getIsDeleted());
		$this->assertNull($event2->getIsDuplicateOfId());


		$user1AtEvent1 = $userAtEventRepo->loadByUserAndEvent($user1, $event1);
		$this->assertNotNull($user1AtEvent1);
		$this->assertEquals(true, $user1AtEvent1->getIsPlanAttending());
		$this->assertEquals(false, $user1AtEvent1->getIsPlanMaybeAttending());
		$this->assertEquals(true, $user1AtEvent1->getIsPlanPublic());

		$user1AtEvent2 = $userAtEventRepo->loadByUserAndEvent($user1, $event2);
		$this->assertNotNull($user1AtEvent2);
		$this->assertEquals(false, $user1AtEvent2->getIsPlanAttending());
		$this->assertEquals(true, $user1AtEvent2->getIsPlanMaybeAttending());
		$this->assertEquals(false, $user1AtEvent2->getIsPlanPublic());

		$user2AtEvent1 = $userAtEventRepo->loadByUserAndEvent($user2, $event1);
		$this->assertNull($user2AtEvent1);

		$user2AtEvent2 = $userAtEventRepo->loadByUserAndEvent($user2, $event2);
		$this->assertNotNull($user2AtEvent2);
		$this->assertEquals(false, $user2AtEvent2->getIsPlanAttending());
		$this->assertEquals(true, $user2AtEvent2->getIsPlanMaybeAttending());
		$this->assertEquals(false, $user2AtEvent2->getIsPlanPublic());


		//==================================================== Mark
		TimeSource::mock(2014,5,1,8,0,0);
		$eventRepository->markDuplicate($event2, $event1, $user);


		//==================================================== Test Duplicate

		$event2 = $eventRepository->loadBySlug($site, $event2->getSlug());
		$this->assertTrue($event2->getIsDeleted());
		$this->assertEquals($event1->getId(), $event2->getIsDuplicateOfId());


		// This should not have changed; as there already was data here we don't change it.
		$user1AtEvent1 = $userAtEventRepo->loadByUserAndEvent($user1, $event1);
		$this->assertNotNull($user1AtEvent1);
		$this->assertEquals(true, $user1AtEvent1->getIsPlanAttending());
		$this->assertEquals(false, $user1AtEvent1->getIsPlanMaybeAttending());
		$this->assertEquals(true, $user1AtEvent1->getIsPlanPublic());

		$user1AtEvent2 = $userAtEventRepo->loadByUserAndEvent($user1, $event2);
		$this->assertNotNull($user1AtEvent2);
		$this->assertEquals(false, $user1AtEvent2->getIsPlanAttending());
		$this->assertEquals(true, $user1AtEvent2->getIsPlanMaybeAttending());
		$this->assertEquals(false, $user1AtEvent2->getIsPlanPublic());

		// This should now change, the mark dupe function should have copied it.
		$user2AtEvent1 = $userAtEventRepo->loadByUserAndEvent($user2, $event1);
		$this->assertNotNull($user2AtEvent1);
		$this->assertEquals(false, $user2AtEvent1->getIsPlanAttending());
		$this->assertEquals(true, $user2AtEvent1->getIsPlanMaybeAttending());
		$this->assertEquals(false, $user2AtEvent1->getIsPlanPublic());

		$user2AtEvent2 = $userAtEventRepo->loadByUserAndEvent($user2, $event2);
		$this->assertNotNull($user2AtEvent2);
		$this->assertEquals(false, $user2AtEvent2->getIsPlanAttending());
		$this->assertEquals(true, $user2AtEvent2->getIsPlanMaybeAttending());
		$this->assertEquals(false, $user2AtEvent2->getIsPlanPublic());

	}


}




