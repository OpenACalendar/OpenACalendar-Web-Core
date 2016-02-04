<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use models\UserAtEventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\UserAtEventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserGetDataForUpcomingEventsEmailTest  extends \BaseAppWithDBTest {

	function testBlank() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		
		list($upcomingEvents, $allEvents, $userAtEvent, $flag) = $user->getDataForUpcomingEventsEmail();
		
		$this->assertEquals(0, count($upcomingEvents));
		$this->assertEquals(0, count($allEvents));
		$this->assertEquals(0, count($userAtEvent));
		$this->assertFalse($flag);
	}
	
	
	
	public function mktime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
		$dt = new \DateTime('', new \DateTimeZone('UTC'));
		$dt->setTime($hour, $minute, $second);
		$dt->setDate($year, $month, $day);
		return $dt;
	}
	
	function dataForTest1() {
		return array(
			array('a','a',true),
			array('a','m',false),
			array('a','n',false),
			array('m','a',true),
			array('m','m',true),
			array('m','n',false),
			array('n','a',false),
		);
	}
	
	/**
     * @dataProvider dataForTest1
     */	
	function test1($emailOption, $goingOption, $result) {	
	
		$this->app['timesource']->mock(2013,8,1,7,0,0);
		
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
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt($this->mktime(2013,8,2,19,0,0));
		$event->setEndAt($this->mktime(2013,8,2,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user);
		
		$user->setEmailUpcomingEventsDaysNotice(1);
		$user->setEmailUpcomingEvents($emailOption);
		$userRepo->editEmailsOptions($user);
		
		if ($goingOption == 'a') {
			$userAtEvent = new UserAtEventModel();
			$userAtEvent->setUserAccountId($user->getId());
			$userAtEvent->setEventId($event->getId());
			$userAtEvent->setIsPlanAttending(true);
			$uaeRepo = new UserAtEventRepository($this->app);
			$uaeRepo->save($userAtEvent);
		} else if ($goingOption == 'm') {
			$userAtEvent = new UserAtEventModel();
			$userAtEvent->setUserAccountId($user->getId());
			$userAtEvent->setEventId($event->getId());
			$userAtEvent->setIsPlanMaybeAttending(true);
			$uaeRepo = new UserAtEventRepository($this->app);
			$uaeRepo->save($userAtEvent);
		}
		
		list($upcomingEvents, $allEvents, $userAtEvent, $flag) = $user->getDataForUpcomingEventsEmail();
		$this->assertEquals($result, $flag);
		
	}
	
}


