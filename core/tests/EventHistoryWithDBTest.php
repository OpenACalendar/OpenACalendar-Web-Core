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
class EventHistoryWithDBTest extends \BaseAppWithDBTest {



	function testIntegration1() {
		$this->app['timesource']->mock(2014, 1, 1, 12, 0, 0);

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

		## Create Event
		$this->app['timesource']->mock(2014, 1, 1, 13, 0, 0);
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,9,1,1,1,1));
		$event->setEndAt(getUTCDateTime(2014,9,1,1,1,1));

		$eventRepo = new EventRepository();
		$eventRepo->create($event, $site, $user);

		## Edit event
		$this->app['timesource']->mock(2014, 1, 1, 14, 0, 0);

		$event = $eventRepo->loadBySlug($site, $event->getSlug());
		$event->setDescription("testy 123");
		$eventRepo->edit($event, $user);

		# delete event
		$this->app['timesource']->mock(2014, 1, 1, 15, 0, 0);
		$eventRepo->delete($event, $user);

		## Now save changed flags on these .....
		$eventHistoryRepo = new EventHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM event_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);
			$eventHistoryRepo->ensureChangedFlagsAreSet($eventHistory);
		}

		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setSite($site);
		$historyRepo->setIncludeEventHistory(true);
		$histories = $historyRepo->fetchAll();

		$this->assertEquals(3, count($histories));

		#the delete
		$this->assertEquals(FALSE, $histories[0]->getSummaryChanged());
		$this->assertEquals(false, $histories[0]->getDescriptionChanged());
		$this->assertEquals(true, $histories[0]->getIsDeletedChanged());

		#the edit
		$this->assertEquals(FALSE, $histories[1]->getSummaryChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());

		#the create
		$this->assertEquals(true, $histories[2]->getSummaryChanged());
		$this->assertEquals(true, $histories[2]->getDescriptionChanged());
		$this->assertEquals(false, $histories[2]->getIsDeletedChanged());

		## Now load history at a certain point; this is to test rollback!
		$history = $eventHistoryRepo->loadByEventAndtimeStamp($event, getUTCDateTime(2014,1,1,15,0,0)->getTimestamp());
		$this->assertEquals("test", $history->getSummary());
		$this->assertEquals("testy 123", $history->getDescription());


	}


}

