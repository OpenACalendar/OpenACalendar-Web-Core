<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportURLModel;
use models\AreaModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportURLRepository;
use repositories\AreaRepository;
use repositories\CountryRepository;
use import\ImportURLRun;
use import\ImportURLICalHandler;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLICALRecurringDeleteTest extends \BaseAppWithDBTest {


	function testRRuleDeleteByExDate1() {
		global $CONFIG;

		\TimeSource::mock(2015, 1, 1, 1, 1, 1);
		$CONFIG->importURLAllowEventsSecondsIntoFuture = 77760000;


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

		$importURLRepository = new ImportURLRepository();

		$importURL = new ImportURLModel();
		$importURL->setIsEnabled(true);
		$importURL->setSiteId($site->getId());
		$importURL->setGroupId($group->getId());
		$importURL->setTitle("Test");
		$importURL->setUrl("http://test.com");

		$importURLRepository->create($importURL, $site, $user);

		// ============================================= Import CREATE
		$importURLRun = new ImportURLRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRuleDeleteByExDate1Part1.ics');
		$i = new ImportURLICalHandler();
		$i->setImportURLRun($importURLRun);
		$i->setLimitToSaveOnEachRun(7);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

		// Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder();
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
		$erb = new EventRepositoryBuilder();
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


		\TimeSource::mock(2015, 1,2, 1, 1, 1);

		// ============================================= Import With no changes
		$importURLRun = new ImportURLRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRuleDeleteByExDate1Part1.ics');
		$i = new ImportURLICalHandler();
		$i->setImportURLRun($importURLRun);
		$i->setLimitToSaveOnEachRun(7);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

		// Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder();
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
		$erb = new EventRepositoryBuilder();
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


		\TimeSource::mock(2015, 1,3, 1, 1, 1);

		// ============================================= Import WITH ONE DELETED!
		$importURLRun = new ImportURLRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRuleDeleteByExDate1Part2.ics');
		$i = new ImportURLICalHandler();
		$i->setImportURLRun($importURLRun);
		$i->setLimitToSaveOnEachRun(7);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

		// Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder();
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
		$erb = new EventRepositoryBuilder();
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

