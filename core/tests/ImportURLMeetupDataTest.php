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
class ImportURLMeetupDataTest extends \BaseAppWithDBTest {

    /**
     *
     * @group import
     */
    function testBasic() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 10, 1, 1, 1, 1);
		$CONFIG->importAllowEventsSecondsIntoFuture = 7776000; // 90 days
		

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
		$importURLRun = new ImportRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/Meetup1.ics');	
		$importURLRun->setFlag(ImportRun::$FLAG_ADD_UIDS);
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
		
		$this->assertEquals("Talk & Build AngularJS",$event->getSummary());
		$this->assertEquals('2013-10-17 18:00:00', $event->getStartAt()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-10-17 21:00:00', $event->getEndAt()->format('Y-m-d H:i:s'));		
		$this->assertEquals("AngularJS - Edinburgh\nThursday, October 17 at 7:00 PM\n\nDetails: http://www.meetup.com/AngularJS-Edinburgh/events/141654792/",$event->getDescription());
		$this->assertEquals('http://www.meetup.com/AngularJS-Edinburgh/events/141654792/',$event->getURL());
		$this->assertFalse($event->getIsDeleted());
		
	}
	
	
}

