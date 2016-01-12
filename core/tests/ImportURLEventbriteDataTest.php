<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportRepository;
use import\ImportRun;
use import\ImportICalHandler;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLEventbriteDataTest extends \BaseAppWithDBTest {

    /**
     *
     * @group import
     */
    function testBasic() {

		$this->app['timesource']->mock(2013, 10, 1, 1, 1, 1);
		$this->app['config']->importAllowEventsSecondsIntoFuture = 7776000; // 90 days

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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$importRepository = new ImportRepository();
		
		$importURL = new ImportModel();
		$importURL->setIsEnabled(true);
		$importURL->setSiteId($site->getId());
		$importURL->setGroupId($group->getId());
		$importURL->setTitle("Test");
		$importURL->setUrl("http://test.com");
		
		$importRepository->create($importURL, $site, $user);
		

		
		// Import
		$importURLRun = new ImportRun($this->app, $importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/Eventbrite1.ical');	
		$importURLRun->setFlag(ImportRun::$FLAG_ADD_UIDS);
		$importURLRun->setFlag(ImportRun::$FLAG_SET_TICKET_URL_AS_URL);
		$i = new ImportICalHandler($this->app);
		$i->setImportRun($importURLRun);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

        $importRunner = new TestsImportRunner($this->app);
        $importRunner->testRunImportedEventsToEvents($importURLRun);

		// Load!
		$erb = new EventRepositoryBuilder();
		$erb->setSite($site);
		$events = $erb->fetchAll();
		$this->assertEquals(1, count($events));
		$event = $events[0];
		
		$this->assertEquals("Computing At School Scotland Conference 2013",$event->getSummary());
		$this->assertEquals('2013-10-26 07:30:00', $event->getStartAt()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-10-26 16:00:00', $event->getEndAt()->format('Y-m-d H:i:s'));		
		$this->assertEquals('For details, click here: https://casscot13.eventbrite.co.uk',$event->getDescription());
		$this->assertEquals('https://casscot13.eventbrite.co.uk',$event->getURL());
		$this->assertEquals('https://casscot13.eventbrite.co.uk',$event->getTicketURL());
		$this->assertFalse($event->getIsDeleted());
		
	}
	
	
}

