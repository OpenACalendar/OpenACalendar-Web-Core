<?php

use models\EventHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\EventHistoryRepository;
use repositories\CountryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class DeleteOldHistoryIpsWithDBTest extends BaseAppWithDBTest
{



    function testEvents1() {
        $this->app['timesource']->mock(2014, 1, 1, 12, 0, 0);

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

        ## Create Event
        $this->app['timesource']->mock(2014, 1, 1, 13, 0, 0);
        $event = new EventModel();
        $event->setSummary("test");
        $event->setDescription("test test");
        $event->setStartAt(getUTCDateTime(2014,9,1,1,1,1));
        $event->setEndAt(getUTCDateTime(2014,9,1,1,1,1));

        $eventMeta = new \models\EventEditMetaDataModel();
        $eventMeta->setUserAccount($user);

        $eventRepo = new EventRepository($this->app);
        $eventRepo->createWithMetaData($event, $site, $eventMeta);

        ## Set IP on event
        $stat = $this->app['db']->prepare(" UPDATE event_history SET from_ip='1.2.3.4' ");
        $stat->execute();

        ## Call Task
        $this->app['config']->taskDeleteOldHistoryIpsDeleteOlderThan = 365*60*60*24; // 1 year
        $this->app['timesource']->mock(2017, 1, 1, 13, 0, 0);

        $task = new \tasks\DeleteOldHistoryIps($this->app);
        $task->runManuallyNowIfShould();


        ## Now load and check

        $stat = $this->app['db']->prepare("SELECT * FROM event_history");
        $stat->execute();
        $data = $stat->fetch(PDO::FETCH_ASSOC);

        $this->assertNull($data['from_ip']);

    }


}
