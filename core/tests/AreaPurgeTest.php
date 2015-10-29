<?php

use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use models\EventModel;
use models\CountryModelModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\EventRepository;
use repositories\AreaRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaPurgeTest extends BaseAppWithDBTest {
	
	function test1() {
		$this->addCountriesToTestDB();
		$countryRepo = new CountryRepository();
		$areaRepo = new AreaRepository();

		\TimeSource::mock(2014,10,1,1,0,0);

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
		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), $this->getSiteQuotaUsedForTesting());

		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");
		
		$areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);

		$areaDuplicate = new AreaModel();
		$areaDuplicate->setTitle("test Duplicate");

		$areaRepo->create($areaDuplicate, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);
		\TimeSource::mock(2014,10,1,2,0,0);
		$areaRepo->markDuplicate($areaDuplicate, $area, $user);

		$areaChild = new AreaModel();
		$areaChild->setTitle("test Child");

		$areaRepo->create($areaChild, $area, $site, $countryRepo->loadByTwoCharCode('GB') , $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event->setAreaId($area->getId());

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$sysadminCommentRepo = new \repositories\SysAdminCommentRepository();
		$sysadminCommentRepo->createAboutArea($area, "TEST", null);

		## Test

		$this->assertNotNull($areaRepo->loadById($area->getId()));

		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertEquals($area->getId(), $event->getAreaId());


		## Now Purge!
		$areaRepo->purge($area);


		## Test
		$this->assertNull($areaRepo->loadById($area->getId()));

		$event = $eventRepository->loadBySlug($site, $event->getSlug());
		$this->assertNull($event->getAreaId());

	}

}




