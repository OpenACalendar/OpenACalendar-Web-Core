<?php

use models\UserAccountModel;
use models\SiteModel;
use models\CuratedListModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CuratedListRepository;
use repositories\EventRepository;
use repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListPurgeTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		$DB = getNewTestDB();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userOther = new UserAccountModel();
		$userOther->setEmail("test2@jarofgreen.co.uk");
		$userOther->setUsername("test2");
		$userOther->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userOther);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);
		
		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");
		
		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);
		$clRepo->addEditorToCuratedList($userOther, $curatedList, $user);
		$clRepo->addEventtoCuratedList($event, $curatedList, $user);
			
		## Test
		$this->assertNotNull($clRepo->loadBySlug($site, $curatedList->getSlug()));		

		## Purge!
		$clRepo->purge($curatedList);
				
		## Test
		$this->assertNull($clRepo->loadBySlug($site, $curatedList->getSlug()));		
				
	}
	
	
}


