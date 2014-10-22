<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportURLModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportURLRepository;
use import\ImportURLRun;
use import\ImportURLICalHandler;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLLanyrdDataTest extends \PHPUnit_Framework_TestCase {

	
	function testBasic() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 10, 1, 1, 1, 1);
		$CONFIG->importURLAllowEventsSecondsIntoFuture = 7776000; // 90 days
		
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
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$importURLRepository = new ImportURLRepository();
		
		$importURL = new ImportURLModel();
		$importURL->setIsEnabled(true);
		$importURL->setSiteId($site->getId());
		$importURL->setGroupId($group->getId());
		$importURL->setTitle("Test");
		$importURL->setUrl("http://test.com");
		
		$importURLRepository->create($importURL, $site, $user);
		

		
		// Import
		$importURLRun = new ImportURLRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/Lanyrd1.ical');	
		$importURLRun->setFlag(ImportURLRun::$FLAG_ADD_UIDS);
		$i = new ImportURLICalHandler();
		$i->setImportURLRun($importURLRun);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

		// Load!
		$erb = new EventRepositoryBuilder();
		$erb->setSite($site);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));
		$event = $events[0];
		
		$this->assertEquals("State of the Map Scotland 2013",$event->getSummary());
		$this->assertEquals('2013-10-11 00:00:00', $event->getStartAt()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-10-14 23:59:59', $event->getEndAt()->format('Y-m-d H:i:s'));		
		$this->assertEquals("http://sotms.eventbrite.com/\n\nhttp://lanyrd.com/crkmt",$event->getDescription());
		$this->assertEquals('http://lanyrd.com/2013/sotmscot2013/',$event->getURL());
		$this->assertFalse($event->getIsDeleted());


		// Look for event
		$erb = new EventRepositoryBuilder();
		$erb->setSite($site);
		$erb->setImportURL($importURL);
		$this->assertEquals(1, count($erb->fetchAll()));

	}
	
	
}

