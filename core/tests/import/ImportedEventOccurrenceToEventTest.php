<?php

namespace tests\import;

use import\ImportedEventOccurrenceToEvent;
use import\ImportedEventToImportedEventOccurrences;
use import\ImportRun;
use models\AreaModel;
use models\EventModel;
use models\EventRecurSetModel;
use models\GroupModel;
use models\ImportedEventModel;
use models\ImportModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\AreaRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\CountryRepository;
use repositories\GroupRepository;
use repositories\ImportedEventRepository;
use repositories\ImportRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventOccurrenceToEventTest extends \BaseAppWithDBTest {



    public function testAreaSetManually() {

        $this->app['timesource']->mock(2016,01,01,10,0,0);

        $this->addCountriesToTestDB();
        $countryRepo = new CountryRepository($this->app);

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


        $areaScotland = new AreaModel();
        $areaScotland->setSiteId($site->getId());
        $areaScotland->setTitle('Scotland');
        $areaScotland->setCountryId($countryRepo->loadByTwoCharCode('GB'));

        $areaRepo = new AreaRepository($this->app);
        $areaRepo->create($areaScotland, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);


        $group = new GroupModel();
        $group->setTitle('Test');

        $groupRepo = new GroupRepository($this->app);
        $groupRepo->create($group, $site, $user);

        $import = new ImportModel();
        $import->setUrl('http://example.org');
        $import->setCountryId($countryRepo->loadByTwoCharCode('GB')->getId());
        $import->setAreaId($areaScotland->getId());

        $importRepo = new ImportRepository($this->app);
        $importRepo->create($import, $site, $user);

        $importedEventModel = new ImportedEventModel();
        $importedEventModel->setImportId($import->getId());
        $importedEventModel->setIdInImport('TEST');
        $importedEventModel->setTitle('Test');
        $importedEventModel->setStartAt(new \DateTime('2016-02-01 09:00:00'));
        $importedEventModel->setEndAt(new \DateTime('2016-02-01 17:00:00'));

        $importedEventRepo = new ImportedEventRepository($this->app);
        $importedEventRepo->create($importedEventModel);

        // RUN

        $importRun = new ImportRun($this->app, $import, $site);

        $importedEventToImportedEventOccurrences = new ImportedEventToImportedEventOccurrences($this->app, $importedEventModel);
        $importedEventOccurrences = $importedEventToImportedEventOccurrences->getImportedEventOccurrences();
        $this->assertEquals(1, count($importedEventOccurrences));

        $importedEventOccurrenceToEvent = new ImportedEventOccurrenceToEvent($this->app, $importRun);
        $importedEventOccurrenceToEvent->run(array_pop($importedEventOccurrences));

        // TEST

        $eventRepoBuilder = new EventRepositoryBuilder($this->app);
        $eventRepoBuilder->setImport($import);
        $events = $eventRepoBuilder->fetchAll();

        $this->assertEquals(1, count($events));

        $event = array_pop($events);

        $this->assertEquals('Test', $event->getSummary());
        // This area was set manually on the importer and should have been carried throught!
        $this->assertEquals($areaScotland->getId(), $event->getAreaId());

    }


    public function testAreaSetByLatLng() {

        $this->app['timesource']->mock(2016,01,01,10,0,0);

        $this->addCountriesToTestDB();
        $countryRepo = new CountryRepository($this->app);

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


        $areaScotland = new AreaModel();
        $areaScotland->setSiteId($site->getId());
        $areaScotland->setTitle('Scotland');
        $areaScotland->setMinLat(55.573169);
        $areaScotland->setMinLng(-6.317139);
        $areaScotland->setMaxLat(60.893935);
        $areaScotland->setMaxLng(-0.604248);
        $areaScotland->setCountryId($countryRepo->loadByTwoCharCode('GB'));

        $areaRepo = new AreaRepository($this->app);
        $areaRepo->create($areaScotland, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);


        $group = new GroupModel();
        $group->setTitle('Test');

        $groupRepo = new GroupRepository($this->app);
        $groupRepo->create($group, $site, $user);

        $import = new ImportModel();
        $import->setUrl('http://example.org');
        $import->setCountryId($countryRepo->loadByTwoCharCode('GB')->getId());
        // NO $import->setAreaId() - we will try and guess automatically

        $importRepo = new ImportRepository($this->app);
        $importRepo->create($import, $site, $user);

        $importedEventModel = new ImportedEventModel();
        $importedEventModel->setImportId($import->getId());
        $importedEventModel->setIdInImport('TEST');
        $importedEventModel->setTitle('Test');
        $importedEventModel->setLng(-2.914585);
        $importedEventModel->setLat(58.962784);
        $importedEventModel->setStartAt(new \DateTime('2016-02-01 09:00:00'));
        $importedEventModel->setEndAt(new \DateTime('2016-02-01 17:00:00'));

        $importedEventRepo = new ImportedEventRepository($this->app);
        $importedEventRepo->create($importedEventModel);

        // RUN

        $importRun = new ImportRun($this->app, $import, $site);

        $importedEventToImportedEventOccurrences = new ImportedEventToImportedEventOccurrences($this->app, $importedEventModel);
        $importedEventOccurrences = $importedEventToImportedEventOccurrences->getImportedEventOccurrences();
        $this->assertEquals(1, count($importedEventOccurrences));

        $importedEventOccurrenceToEvent = new ImportedEventOccurrenceToEvent($this->app, $importRun);
        $importedEventOccurrenceToEvent->run(array_pop($importedEventOccurrences));

        // TEST

        $eventRepoBuilder = new EventRepositoryBuilder($this->app);
        $eventRepoBuilder->setImport($import);
        $events = $eventRepoBuilder->fetchAll();

        $this->assertEquals(1, count($events));

        $event = array_pop($events);

        $this->assertEquals('Test', $event->getSummary());
        // the area was set automatically by the lat lng!
        $this->assertEquals($areaScotland->getId(), $event->getAreaId());

    }


}


