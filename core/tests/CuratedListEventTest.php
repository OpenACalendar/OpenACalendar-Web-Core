<?php

use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use models\CuratedListModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\CuratedListRepository;
use repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListEventTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		$DB = getNewTestDB();

		TimeSource::mock(2014,5,1,7,0,0);

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
		
		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");
		
		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);


		// Test Before
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder();
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));


		// Add event to list
		TimeSource::mock(2014,5,1,8,0,0);
		$clRepo->addEventtoCuratedList($event, $curatedList, $user);


		// Test After
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder();
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(1, count($eventRepositoryBuilder->fetchAll()));


		// Remove event to list
		TimeSource::mock(2014,5,1,9,0,0);
		$clRepo->removeEventFromCuratedList($event, $curatedList, $user);


		// Test After
		$eventRepositoryBuilder = new \repositories\builders\EventRepositoryBuilder();
		$eventRepositoryBuilder->setCuratedList($curatedList);
		$this->assertEquals(0, count($eventRepositoryBuilder->fetchAll()));

	}

	
}


