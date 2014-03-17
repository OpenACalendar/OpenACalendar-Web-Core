<?php

use models\EventModel;
use models\EventRecurSetModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelGetNewWeeklyEventsTest extends \PHPUnit_Framework_TestCase {
	
	public function mktime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
		$dt = new \DateTime('', new \DateTimeZone('UTC'));
		$dt->setTime($hour, $minute, $second);
		$dt->setDate($year, $month, $day);
		return $dt;
	}
	
	
	/*
	 * Basic test. In winter.
	 * 
	 */
	function test1() {
		
		TimeSource::mock(2012,12,1,9,0,0);
		
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,12,5,19,0,0));
		$event->setEndAt($this->mktime(2012,12,5,21,0,0));		
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewWeeklyEvents($event, 2);
		
		$this->assertTrue(count($newEvents) >= 2);
		
		$this->assertEquals($this->mktime(2012,12,12,19,0,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,12,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2012,12,19,19,0,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,19,21,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		
	}
	
	/*
	 * Basic test. In summer.
	 * 
	 */
	function test2() {
		
		TimeSource::mock(2012,6,1,9,0,0);
		
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,6,5,19,0,0));
		$event->setEndAt($this->mktime(2012,6,5,21,0,0));
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewWeeklyEvents($event, 2);
		
		$this->assertTrue(count($newEvents) >= 2);
		
		$this->assertEquals($this->mktime(2012,6,12,19,0,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,6,12,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2012,6,19,19,0,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,6,19,21,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		
	}
	
	
	
	/*
	 * Basic test. Across DST change.
	 * 
	 */
	function test3() {
		
		TimeSource::mock(2013,3,1,9,0,0);
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2013,3,1,19,0,0));
		$event->setEndAt($this->mktime(2013,3,1,21,0,0));
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewWeeklyEvents($event, 2);
		
		$this->assertTrue(count($newEvents) >= 8);
		
		$this->assertEquals($this->mktime(2013,3,8,19,0,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,3,8,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2013,3,15,19,0,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,3,15,21,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2013,3,22,19,0,0)->format('r'), $newEvents[2]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,3,22,21,0,0)->format('r'), $newEvents[2]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2013,3,29,19,0,0)->format('r'), $newEvents[3]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,3,29,21,0,0)->format('r'), $newEvents[3]->getEndAt()->format('r'));
		
	### at his point DST kicks in and since we are getting data times (in UTC) they shifn by an hour.
		$this->assertEquals($this->mktime(2013,4,5,18,0,0)->format('r'), $newEvents[4]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,4,5,20,0,0)->format('r'), $newEvents[4]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2013,4,12,18,0,0)->format('r'), $newEvents[5]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,4,12,20,0,0)->format('r'), $newEvents[5]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2013,4,19,18,0,0)->format('r'), $newEvents[6]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,4,19,20,0,0)->format('r'), $newEvents[6]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2013,4,26,18,0,0)->format('r'), $newEvents[7]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2013,4,26,20,0,0)->format('r'), $newEvents[7]->getEndAt()->format('r'));
		
		
	}
	
	
	
}