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
class ImportURLICALTest extends \BaseAppWithDBTest {

    /**
     *
     * @group import
     */
    function testBasicThenDeletedByFlag() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 10, 1, 1, 1, 1);
		$CONFIG->importAllowEventsSecondsIntoFuture = 7776000; // 90 days
        $this->app['config']->importLimitToSaveOnEachRunImportedEvents = 1000;
        $this->app['config']->importLimitToSaveOnEachRunEvents = 100;
		
		$this->addCountriesToTestDB();
		$countryRepo = new CountryRepository();

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
		$siteRepo->create($site, $user, array(  $countryRepo->loadByTwoCharCode('GB')  ), $this->getSiteQuotaUsedForTesting());
		
		
		$areaRepo = new AreaRepository();
		
		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");
		
		$areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);
		
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
		$importURL->setCountryId($countryRepo->loadByTwoCharCode('GB')->getId());
		$importURL->setAreaId($area->getId());
		$importURL->setTitle("Test");
		$importURL->setUrl("http://test.com");
		
		$importRepository->create($importURL, $site, $user);
		

		
		// Import
		$importURLRun = new ImportRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/BasicICAL.ical');		
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
		
		$this->assertEquals("Test 3 SpecFic Writing Group",$event->getSummary());
		$this->assertEquals('2013-11-12 18:00:00', $event->getStartAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-11-12 20:30:00', $event->getEndAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getDescription());
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getURL());
		$this->assertFalse($event->getIsDeleted());
		$this->assertEquals($countryRepo->loadByTwoCharCode('GB')->getId(), $event->getCountryId());
		$this->assertEquals($area->getId(), $event->getAreaId());
		$this->assertEquals("Europe/London",$event->getTimezone());
		
		// Import again
		\TimeSource::mock(2013, 10, 1, 1, 1, 2);
		$importURLRun = new ImportRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/BasicICALDeleted.ical');		
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
		
		$this->assertTrue($event->getIsDeleted());
		
	}

    /**
     *
     * @group import
     */
    function testBasicThenDeletedByVanishing() {
		global $CONFIG;

		\TimeSource::mock(2013, 10, 1, 1, 1, 1);
		$CONFIG->importAllowEventsSecondsIntoFuture = 7776000; // 90 days
        $this->app['config']->importLimitToSaveOnEachRunImportedEvents = 1000;
        $this->app['config']->importLimitToSaveOnEachRunEvents = 100;

		$this->addCountriesToTestDB();
		$countryRepo = new CountryRepository();

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
		$siteRepo->create($site, $user, array(  $countryRepo->loadByTwoCharCode('GB')  ), $this->getSiteQuotaUsedForTesting());


		$areaRepo = new AreaRepository();

		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");

		$areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);

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
		$importURL->setCountryId($countryRepo->loadByTwoCharCode('GB')->getId());
		$importURL->setAreaId($area->getId());
		$importURL->setTitle("Test");
		$importURL->setUrl("http://test.com");

		$importRepository->create($importURL, $site, $user);



		// Import
		$importURLRun = new ImportRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/BasicICAL.ical');
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

		$this->assertEquals("Test 3 SpecFic Writing Group",$event->getSummary());
		$this->assertEquals('2013-11-12 18:00:00', $event->getStartAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-11-12 20:30:00', $event->getEndAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getDescription());
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getURL());
		$this->assertFalse($event->getIsDeleted());
		$this->assertEquals($countryRepo->loadByTwoCharCode('GB')->getId(), $event->getCountryId());
		$this->assertEquals($area->getId(), $event->getAreaId());
		$this->assertEquals("Europe/London",$event->getTimezone());

		// Import again
		\TimeSource::mock(2013, 10, 1, 1, 1, 2);
		$importURLRun = new ImportRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/BasicICALNoEvents.ical');
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

		$this->assertTrue($event->getIsDeleted());

	}

    /**
     *
     * @group import
     */
    function testMoves() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 10, 1, 1, 1, 1);
		$CONFIG->importAllowEventsSecondsIntoFuture = 7776000; // 90 days
        $this->app['config']->importLimitToSaveOnEachRunImportedEvents = 1000;
        $this->app['config']->importLimitToSaveOnEachRunEvents = 100;
		

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
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/MovedICALPart1.ical');		
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
		
		$this->assertEquals("Test 3 SpecFic Writing Group",$event->getSummary());
		$this->assertEquals('2013-11-12 18:00:00', $event->getStartAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-11-12 20:30:00', $event->getEndAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getDescription());
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getURL());
		$this->assertFalse($event->getIsDeleted());
		
		// Import again
		\TimeSource::mock(2013, 10, 1, 1, 1, 2);
		$importURLRun = new ImportRun($importURL, $site);
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/MovedICALPart2.ical');		
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
		
		$this->assertEquals("Test 3 SpecFic Writing Group",$event->getSummary());
		$this->assertEquals('2013-11-12 18:30:00', $event->getStartAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('2013-11-12 20:30:00', $event->getEndAtInUTC()->format('Y-m-d H:i:s'));
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getDescription());
		$this->assertEquals('http://opentechcalendar.co.uk/index.php/event/166',$event->getURL());
		$this->assertFalse($event->getIsDeleted());
		
	}

    /**
     *
     * @group import
     */
    function testNotValid() {
		global $CONFIG;
		
		\TimeSource::mock(2013, 10, 1, 1, 1, 1);
		$CONFIG->importAllowEventsSecondsIntoFuture = 7776000; // 90 days
        $this->app['config']->importLimitToSaveOnEachRunImportedEvents = 1000;
        $this->app['config']->importLimitToSaveOnEachRunEvents = 100;
		

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
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ICALNotValid.ical');		
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
		$this->assertEquals(0, count($events));
		
	}

    /**
     *
     * @group import
     */
    function testLimits() {
		global $CONFIG;

        \TimeSource::mock(2012, 9, 1, 1, 1, 1);
        $CONFIG->importAllowEventsSecondsIntoFuture = 77760000;
        $this->app['config']->importLimitToSaveOnEachRunImportedEvents = 1000;
        $this->app['config']->importLimitToSaveOnEachRunEvents = 2;
		

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
		$importURLRun->setTemporaryFileStorageForTesting(dirname(__FILE__).'/data/ICALManyEvents.ical');		
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
		$this->assertEquals(2, count($events));
		
		
		
	}

	
}

