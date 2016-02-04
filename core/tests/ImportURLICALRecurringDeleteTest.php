<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportModel;
use models\AreaModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportRepository;
use repositories\AreaRepository;
use repositories\CountryRepository;
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
class ImportURLICALRecurringDeleteTest extends \BaseAppWithDBTest {


    /**
     *
     * @group import
     */
    function testRRuleDeleteByExDate1() {

		$this->app['timesource']->mock(2015, 1, 1, 1, 1, 1);
		$this->app['config']->importAllowEventsSecondsIntoFuture = 77760000;
        $this->app['config']->importLimitToSaveOnEachRunImportedEvents = 1000;
        $this->app['config']->importLimitToSaveOnEachRunEvents = 10;


		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");

		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);

		$importRepository = new ImportRepository($this->app);

		$importURL = new ImportModel();
		$importURL->setIsEnabled(true);
		$importURL->setSiteId($site->getId());
		$importURL->setGroupId($group->getId());
		$importURL->setTitle("Test");
		$importURL->setUrl("http://test.com");

		$importRepository->create($importURL, $site, $user);

		// ============================================= Import CREATE
		$importURLRun = new ImportRun($this->app, $importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRuleDeleteByExDate1Part1.ics');
		$i = new ImportICalHandler($this->app);
		$i->setImportRun($importURLRun);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

        $importRunner = new TestsImportRunner($this->app);
        $importRunner->testRunImportedEventsToEvents($importURLRun);

        // Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder($this->app);
		$importedEvents = $ierb->fetchAll();
		$this->assertEquals(1, count($importedEvents));
		$importedEvent = $importedEvents[0];

		$reoccur = $importedEvent->getReoccur();
		$this->assertEquals(true, is_array($reoccur));
		$this->assertEquals(true, isset($reoccur['ical_rrule']));
		$this->assertEquals(true, is_array($reoccur['ical_rrule']));
		$this->assertEquals("WEEKLY", $reoccur['ical_rrule']["FREQ"]);
		$this->assertEquals("TH", $reoccur['ical_rrule']["BYDAY"]);

		// now test real events
		$erb = new EventRepositoryBuilder($this->app);
		$erb->setImportedEvent($importedEvent);
		$erb->setAfterNow();
		$events = $erb->fetchAll();

		$this->assertTrue(count($events) > 5);

		$event = $events[0];
		$this->assertEquals("2015-02-12T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-12T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());

		$event = $events[1];
		$this->assertEquals("2015-02-26T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-26T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());

		$event = $events[2];
		$this->assertEquals("2015-03-12T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-12T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());


		$this->app['timesource']->mock(2015, 1,2, 1, 1, 1);

		// ============================================= Import With no changes
		$importURLRun = new ImportRun($this->app, $importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRuleDeleteByExDate1Part1.ics');
		$i = new ImportICalHandler($this->app);
		$i->setImportRun($importURLRun);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

        $importRunner = new TestsImportRunner($this->app);
        $importRunner->testRunImportedEventsToEvents($importURLRun);

		// Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder($this->app);
		$importedEvents = $ierb->fetchAll();
		$this->assertEquals(1, count($importedEvents));
		$importedEvent = $importedEvents[0];

		$reoccur = $importedEvent->getReoccur();
		$this->assertEquals(true, is_array($reoccur));
		$this->assertEquals(true, isset($reoccur['ical_rrule']));
		$this->assertEquals(true, is_array($reoccur['ical_rrule']));
		$this->assertEquals("WEEKLY", $reoccur['ical_rrule']["FREQ"]);
		$this->assertEquals("TH", $reoccur['ical_rrule']["BYDAY"]);

		// now test real events
		$erb = new EventRepositoryBuilder($this->app);
		$erb->setImportedEvent($importedEvent);
		$erb->setAfterNow();
		$events = $erb->fetchAll();

		$this->assertTrue(count($events) > 5);

		$event = $events[0];
		$this->assertEquals("2015-02-12T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-12T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());

		$event = $events[1];
		$this->assertEquals("2015-02-26T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-26T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());

		$event = $events[2];
		$this->assertEquals("2015-03-12T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-12T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());


		$this->app['timesource']->mock(2015, 1,3, 1, 1, 1);

		// ============================================= Import WITH ONE DELETED!
		$importURLRun = new ImportRun($this->app, $importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRuleDeleteByExDate1Part2.ics');
		$i = new ImportICalHandler($this->app);
		$i->setImportRun($importURLRun);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

        $importRunner = new TestsImportRunner($this->app);
        $importRunner->testRunImportedEventsToEvents($importURLRun);

		// Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder($this->app);
		$importedEvents = $ierb->fetchAll();
		$this->assertEquals(1, count($importedEvents));
		$importedEvent = $importedEvents[0];

		$reoccur = $importedEvent->getReoccur();
		$this->assertEquals(true, is_array($reoccur));
		$this->assertEquals(true, isset($reoccur['ical_rrule']));
		$this->assertEquals(true, is_array($reoccur['ical_rrule']));
		$this->assertEquals("WEEKLY", $reoccur['ical_rrule']["FREQ"]);
		$this->assertEquals("TH", $reoccur['ical_rrule']["BYDAY"]);

		// now test real events
		$erb = new EventRepositoryBuilder($this->app);
		$erb->setImportedEvent($importedEvent);
		$erb->setAfterNow();
		$erb->setIncludeDeleted(true);
		$events = $erb->fetchAll();

		$this->assertTrue(count($events) > 5);

		$event = $events[0];
		$this->assertEquals("2015-02-12T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-12T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());

		$event = $events[1];
		$this->assertEquals("2015-02-26T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-26T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertTrue($event->getIsDeleted());

		$event = $events[2];
		$this->assertEquals("2015-03-12T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-12T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));
		$this->assertFalse($event->getIsDeleted());

	}

}

