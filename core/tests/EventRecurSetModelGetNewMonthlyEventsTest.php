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
class EventRecurSetModelGetNewMontlyEventsTest extends \PHPUnit_Framework_TestCase {
	
	public function mktime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
		$dt = new \DateTime('', new \DateTimeZone('UTC'));
		$dt->setTime($hour, $minute, $second);
		$dt->setDate($year, $month, $day);
		return $dt;
	}
	
	
	/*
	 * Basic test. 2nd Wed. 
	 * 
	 */
	function testNoTitleChange() {
		
		TimeSource::mock(2012,7,1,7,0,0);
		
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,6,13,19,0,0));
		$event->setEndAt($this->mktime(2012,6,13,21,0,0));		
		$event->setSummary("Event Please");
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewMonthlyEvents($event, 6);
		
		$this->assertTrue(count($newEvents) >= 6);
		
		$this->assertEquals($this->mktime(2012,7,11,19,0,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,7,11,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		$this->assertEquals("Event Please", $newEvents[0]->getSummary());
		
		$this->assertEquals($this->mktime(2012,8,8,19,0,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,8,8,21,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		$this->assertEquals("Event Please", $newEvents[1]->getSummary());
		
		$this->assertEquals($this->mktime(2012,9,12,19,0,0)->format('r'), $newEvents[2]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,9,12,21,0,0)->format('r'), $newEvents[2]->getEndAt()->format('r'));
		$this->assertEquals("Event Please", $newEvents[2]->getSummary());
		
		$this->assertEquals($this->mktime(2012,10,10,19,0,0)->format('r'), $newEvents[3]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,10,10,21,0,0)->format('r'), $newEvents[3]->getEndAt()->format('r'));
		$this->assertEquals("Event Please", $newEvents[3]->getSummary());
		
		// DST shift happens here!
		$this->assertEquals($this->mktime(2012,11,14,20,0,0)->format('r'), $newEvents[4]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,11,14,22,0,0)->format('r'), $newEvents[4]->getEndAt()->format('r'));
		$this->assertEquals("Event Please", $newEvents[4]->getSummary());
		
		$this->assertEquals($this->mktime(2012,12,12,20,0,0)->format('r'), $newEvents[5]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,12,22,0,0)->format('r'), $newEvents[5]->getEndAt()->format('r'));
		$this->assertEquals("Event Please", $newEvents[5]->getSummary());
		
	}
	
	
	/*
	 * Basic test. 2nd Wed. 
	 * 
	 */
	function testLongTitleChange() {
		
		TimeSource::mock(2012,7,1,7,0,0);
		
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,6,13,19,0,0));
		$event->setEndAt($this->mktime(2012,6,13,21,0,0));		
		$event->setSummary("Event For June Please");
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewMonthlyEvents($event, 6);
		
		$this->assertTrue(count($newEvents) >= 6);
		
		$this->assertEquals($this->mktime(2012,7,11,19,0,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,7,11,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		$this->assertEquals("Event For July Please", $newEvents[0]->getSummary());
		
		$this->assertEquals($this->mktime(2012,8,8,19,0,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,8,8,21,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		$this->assertEquals("Event For August Please", $newEvents[1]->getSummary());
		
		$this->assertEquals($this->mktime(2012,9,12,19,0,0)->format('r'), $newEvents[2]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,9,12,21,0,0)->format('r'), $newEvents[2]->getEndAt()->format('r'));
		$this->assertEquals("Event For September Please", $newEvents[2]->getSummary());
		
		$this->assertEquals($this->mktime(2012,10,10,19,0,0)->format('r'), $newEvents[3]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,10,10,21,0,0)->format('r'), $newEvents[3]->getEndAt()->format('r'));
		$this->assertEquals("Event For October Please", $newEvents[3]->getSummary());
		
		// DST shift happens here!
		$this->assertEquals($this->mktime(2012,11,14,20,0,0)->format('r'), $newEvents[4]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,11,14,22,0,0)->format('r'), $newEvents[4]->getEndAt()->format('r'));
		$this->assertEquals("Event For November Please", $newEvents[4]->getSummary());
		
		$this->assertEquals($this->mktime(2012,12,12,20,0,0)->format('r'), $newEvents[5]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,12,22,0,0)->format('r'), $newEvents[5]->getEndAt()->format('r'));
		$this->assertEquals("Event For December Please", $newEvents[5]->getSummary());
		
	}
	
	
	
	/*
	 * Basic test. 2nd Wed. 
	 * 
	 */
	function testShortTitleChange() {
		
		TimeSource::mock(2012,7,1,7,0,0);
		
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,6,13,19,0,0));
		$event->setEndAt($this->mktime(2012,6,13,21,0,0));		
		$event->setSummary("Event For Jun Please");
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewMonthlyEvents($event, 6);
		
		$this->assertTrue(count($newEvents) >= 6);
		
		$this->assertEquals($this->mktime(2012,7,11,19,0,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,7,11,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		$this->assertEquals("Event For Jul Please", $newEvents[0]->getSummary());
		
		$this->assertEquals($this->mktime(2012,8,8,19,0,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,8,8,21,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		$this->assertEquals("Event For Aug Please", $newEvents[1]->getSummary());
		
		$this->assertEquals($this->mktime(2012,9,12,19,0,0)->format('r'), $newEvents[2]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,9,12,21,0,0)->format('r'), $newEvents[2]->getEndAt()->format('r'));
		$this->assertEquals("Event For Sep Please", $newEvents[2]->getSummary());
		
		$this->assertEquals($this->mktime(2012,10,10,19,0,0)->format('r'), $newEvents[3]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,10,10,21,0,0)->format('r'), $newEvents[3]->getEndAt()->format('r'));
		$this->assertEquals("Event For Oct Please", $newEvents[3]->getSummary());
		
		// DST shift happens here!
		$this->assertEquals($this->mktime(2012,11,14,20,0,0)->format('r'), $newEvents[4]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,11,14,22,0,0)->format('r'), $newEvents[4]->getEndAt()->format('r'));
		$this->assertEquals("Event For Nov Please", $newEvents[4]->getSummary());
		
		$this->assertEquals($this->mktime(2012,12,12,20,0,0)->format('r'), $newEvents[5]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,12,22,0,0)->format('r'), $newEvents[5]->getEndAt()->format('r'));
		$this->assertEquals("Event For Dec Please", $newEvents[5]->getSummary());
		
	}
	
	
	
	/** test event on 1st sat in month which also happens to be the 1st of the month. **/
	function testFirstWeekInMonthAlsoFirstDayInMonth() {
		
		TimeSource::mock(2012,9,20,14,27,0);
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,9,1,18,30,0));
		$event->setEndAt($this->mktime(2012,9,1,21,0,0));		
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewMonthlyEvents($event, 6);
		
		$this->assertTrue(count($newEvents) >= 6);
		
		$this->assertEquals($this->mktime(2012,10,6,18,30,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,10,6,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2012,11,3,19,30,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,11,3,22,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2012,12,1,19,30,0)->format('r'), $newEvents[2]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,1,22,0,0)->format('r'), $newEvents[2]->getEndAt()->format('r'));
		
		
	}

		
	/** test event on 1st sun in month. This is not the 1st of the month. **/
	function testFirstWeekInMonthButNotFirstDayInMonth() {

		TimeSource::mock(2012,9,20,14,27,0);
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,9,2,18,30,0));
		$event->setEndAt($this->mktime(2012,9,2,21,0,0));		
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewMonthlyEvents($event, 6);
		
		$this->assertTrue(count($newEvents) >= 6);
		
		$this->assertEquals($this->mktime(2012,10,7,18,30,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,10,7,21,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2012,11,4,19,30,0)->format('r'), $newEvents[1]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,11,4,22,0,0)->format('r'), $newEvents[1]->getEndAt()->format('r'));
		
		$this->assertEquals($this->mktime(2012,12,2,19,30,0)->format('r'), $newEvents[2]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,2,22,0,0)->format('r'), $newEvents[2]->getEndAt()->format('r'));
		
	}

	/** test event on 5TH sat in month. **/
	function testFiveWeekInMonth() {
		
		
		TimeSource::mock(2012,9,20,14,27,0);
		
		$event = new EventModel();
		$event->setStartAt($this->mktime(2012,9,29,18,30,0));
		$event->setEndAt($this->mktime(2012,9,29,21,0,0));		
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$newEvents = $eventSet->getNewMonthlyEvents($event, 6);
		
		$this->assertTrue(count($newEvents) >= 1);
		
		$this->assertEquals($this->mktime(2012,12,29,19,30,0)->format('r'), $newEvents[0]->getStartAt()->format('r'));
		$this->assertEquals($this->mktime(2012,12,29,22,0,0)->format('r'), $newEvents[0]->getEndAt()->format('r'));

	}
	
	
}

