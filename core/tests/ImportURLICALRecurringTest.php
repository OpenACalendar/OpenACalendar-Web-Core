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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLICALRecurringTest extends \PHPUnit_Framework_TestCase {

	function testRRule1() {
		global $CONFIG;

		\TimeSource::mock(2014, 11, 17, 1, 1, 1);
		$CONFIG->importURLAllowEventsSecondsIntoFuture = 77760000;

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
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRule1.ics');
		$i = new ImportURLICalHandler();
		$i->setImportURLRun($importURLRun);
		$i->setLimitToSaveOnEachRun(8);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

		// Is it loaded on Imported Events?
		$ierb = new \repositories\builders\ImportedEventRepositoryBuilder();
		$importedEvents = $ierb->fetchAll();
		$this->assertEquals(1, count($importedEvents));
		$importedEvent = $importedEvents[0];

		$this->assertEquals("WEEKLY", $importedEvent->getIcsRrule1()["FREQ"]);
		$this->assertEquals("WE", $importedEvent->getIcsRrule1()["BYDAY"]);


		// Now test real events
		$erb = new EventRepositoryBuilder();
		$erb->setImportedEvent($importedEvent);
		$erb->setAfterNow();
		$events = $erb->fetchAll();

		$this->assertEquals(8, count($events));

		$event = $events[0];
		$this->assertEquals("2014-11-19T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-11-19T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[1];
		$this->assertEquals("2014-11-26T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-11-26T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[2];
		$this->assertEquals("2014-12-03T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-12-03T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[3];
		$this->assertEquals("2014-12-10T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-12-10T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[4];
		$this->assertEquals("2014-12-17T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-12-17T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[5];
		$this->assertEquals("2014-12-24T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-12-24T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[6];
		$this->assertEquals("2014-12-31T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-12-31T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[7];
		$this->assertEquals("2015-01-07T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-01-07T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));


		// Now move time on
		\TimeSource::mock(2014, 12, 25, 1, 1, 1);

		// reimport
		$importURLRun = new ImportURLRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRule1.ics');
		$i = new ImportURLICalHandler();
		$i->setImportURLRun($importURLRun);
		$i->setLimitToSaveOnEachRun(8);
		$this->assertTrue($i->canHandle());
		$r =  $i->handle();

		// Now test real events
		$erb = new EventRepositoryBuilder();
		$erb->setImportedEvent($importedEvent);
		$erb->setAfterNow();
		$events = $erb->fetchAll();

		$this->assertEquals(10, count($events));

		// ... these 2 were created on the first run
		$event = $events[0];
		$this->assertEquals("2014-12-31T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2014-12-31T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[1];
		$this->assertEquals("2015-01-07T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-01-07T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		// ... these 8 were created on the second run
		$event = $events[2];
		$this->assertEquals("2015-01-14T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-01-14T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[3];
		$this->assertEquals("2015-01-21T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-01-21T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[4];
		$this->assertEquals("2015-01-28T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-01-28T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[5];
		$this->assertEquals("2015-02-04T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-04T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[6];
		$this->assertEquals("2015-02-11T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-11T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[7];
		$this->assertEquals("2015-02-18T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-18T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[8];
		$this->assertEquals("2015-02-25T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-02-25T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[9];
		$this->assertEquals("2015-03-04T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-04T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));


	}

	function testRRuleBST1() {
		global $CONFIG;

		\TimeSource::mock(2015, 3, 1, 1, 1, 1);
		$CONFIG->importURLAllowEventsSecondsIntoFuture = 77760000;

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
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ImportRRule1.ics');
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

		$this->assertEquals("WEEKLY", $importedEvent->getIcsRrule1()["FREQ"]);
		$this->assertEquals("WE", $importedEvent->getIcsRrule1()["BYDAY"]);

		// Now test real events
		$erb = new EventRepositoryBuilder();
		$erb->setImportedEvent($importedEvent);
		$erb->setAfterNow();
		$events = $erb->fetchAll();

		$this->assertEquals(7, count($events));

		$event = $events[0];
		$this->assertEquals("2015-03-04T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-04T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[1];
		$this->assertEquals("2015-03-11T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-11T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[2];
		$this->assertEquals("2015-03-18T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-18T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[3];
		$this->assertEquals("2015-03-25T09:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-03-25T10:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		// BST shift just happened! Times should change by 1 hour.

		$event = $events[4];
		$this->assertEquals("2015-04-01T08:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-04-01T09:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[5];
		$this->assertEquals("2015-04-08T08:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-04-08T09:00:00+00:00", $event->getEndAtInUTC()->format("c"));

		$event = $events[6];
		$this->assertEquals("2015-04-15T08:00:00+00:00", $event->getStartAtInUTC()->format("c"));
		$this->assertEquals("2015-04-15T09:00:00+00:00", $event->getEndAtInUTC()->format("c"));

	}
	
}

