<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\CountryRepository;
use repositories\SiteRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\GroupRepository;
use cliapi1\CreateEvent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class CLIAPI1CreateEventTest  extends \BaseAppWithDBTest {

	/**
	 * Default country and timezone
	 */
	function testBasic1() {
		
		\TimeSource::mock(2014,06,01,00,00,00);
		
		$this->addCountriesToTestDB();

		$countryRepo = new CountryRepository();
		$country = $countryRepo->loadByTwoCharCode("GB");

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
		
		
		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"start":{
			"str":"2014-07-01 10:00:00"
		},
		"end":{
			"str":"2014-07-01 17:00:00"
		}
	},
	"site":{
		"slug":"test"
	},
	"group":{
		"slug":"'.$group->getSlug().'"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);
		
		$this->assertEquals(true, $createEvent->canGo());
		
		$createEvent->go();
		
		$eventRepoBuilder = new EventRepositoryBuilder();
		$events = $eventRepoBuilder->fetchAll();
	
		$this->assertEquals(1, count($events));
		
		$event = $events[0];
		
		$this->assertEquals('Test',$event->getSummary());
		$this->assertEquals('test test test', $event->getDescription());
		$this->assertEquals('http://example.com', $event->getUrl());
		// Times above are BST, These are UTC
		$this->assertEquals('2014-07-01T09:00:00+00:00', $event->getStartAtInUTC()->format('c'));
		$this->assertEquals('2014-07-01T16:00:00+00:00', $event->getEndAtInUTC()->format('c'));
		$this->assertEquals($group->getId(), $event->getGroupId());
		$this->assertEquals('Europe/London', $event->getTimeZone());
		$this->assertEquals($country->getId(), $event->getCountryId());
	}

	function testSetCountryAndTimeZone() {

		\TimeSource::mock(2014,06,01,00,00,00);

		$this->addCountriesToTestDB();

		$countryRepo = new CountryRepository();
		$country = $countryRepo->loadByTwoCharCode("DE");

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


		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"start":{
			"str":"2014-07-01 10:00:00"
		},
		"end":{
			"str":"2014-07-01 17:00:00"
		},
		"country":{
			"code":"DE"
		},
		"timezone":"Europe/Berlin"
	},
	"site":{
		"slug":"test"
	},
	"group":{
		"slug":"'.$group->getSlug().'"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);

		$this->assertEquals(true, $createEvent->canGo());

		$createEvent->go();

		$eventRepoBuilder = new EventRepositoryBuilder();
		$events = $eventRepoBuilder->fetchAll();

		$this->assertEquals(1, count($events));

		$event = $events[0];

		$this->assertEquals('Test',$event->getSummary());
		$this->assertEquals('test test test', $event->getDescription());
		$this->assertEquals('http://example.com', $event->getUrl());
		// Times above are Berlin, These are UTC
		$this->assertEquals('2014-07-01T09:00:00+00:00', $event->getStartAtInUTC()->format('c'));
		$this->assertEquals('2014-07-01T16:00:00+00:00', $event->getEndAtInUTC()->format('c'));
		$this->assertEquals($group->getId(), $event->getGroupId());
		$this->assertEquals('Europe/Berlin', $event->getTimeZone());
		$this->assertEquals($country->getId(), $event->getCountryId());
	}

	function testValidateBadCountry() {
		
		\TimeSource::mock(2014,06,01,00,00,00);
		
		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"start":{
			"str":"2014-07-01 10:00:00"
		},
		"end":{
			"str":"2014-07-01 17:00:00"
		},
		"country":{
			"code":"XXEHEH"
		},
		"timezone":"Europe/Berlin"
	},
	"site":{
		"slug":"test"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);
		
		$this->assertEquals(false, $createEvent->canGo());
		
	}


	function testValidateBadTimeZone() {

		\TimeSource::mock(2014,06,01,00,00,00);

		$this->addCountriesToTestDB();

		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"start":{
			"str":"2014-07-01 10:00:00"
		},
		"end":{
			"str":"2014-07-01 17:00:00"
		},
		"country":{
			"code":"GB"
		},
		"timezone":"Europe/MANCHESTERHASITSOWNTIMECOSWEROCK"
	},
	"site":{
		"slug":"test"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);

		$this->assertEquals(false, $createEvent->canGo());

	}


	function testValidateNoStart() {

		\TimeSource::mock(2014,06,01,00,00,00);

		$this->addCountriesToTestDB();

		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"end":{
			"str":"2014-07-01 17:00:00"
		}
	},
	"site":{
		"slug":"test"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);

		$this->assertEquals(false, $createEvent->canGo());

	}
	
	
	function testValidateNoEnd() {
		
		\TimeSource::mock(2014,06,01,00,00,00);

		$this->addCountriesToTestDB();

		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"start":{
			"str":"2014-07-01 10:00:00"
		}
	},
	"site":{
		"slug":"test"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);
		
		$this->assertEquals(false, $createEvent->canGo());
		
	}
	
	
	function testValidateStartAfterEnd() {
		
		\TimeSource::mock(2014,06,01,00,00,00);

		$this->addCountriesToTestDB();

		$json = json_decode('{
	"event":{
		"summary":"Test",
		"description":"test test test",
		"url":"http://example.com",
		"start":{
			"str":"2014-07-01 18:00:00"
		},
		"end":{
			"str":"2014-07-01 17:00:00"
		}
	},
	"site":{
		"slug":"test"
	},
	"user":{
		"username":"test"
	}
}');

		$createEvent = new CreateEvent();
		$createEvent->setFromJSON($json);
		
		$this->assertEquals(false, $createEvent->canGo());
		
	}
}


