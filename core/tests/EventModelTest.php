<?php

use models\EventModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventModelTest extends \PHPUnit_Framework_TestCase {
	
	
	
	function testNoStart() {
		$event = new EventModel();
		$event->setEndAt(getUTCDateTime(2013,08,01,17,0,0));
		$this->assertEquals(false, $event->validate());
	}
	
	
	function testNoEnd() {
		$event = new EventModel();
		$event->setStartAt(getUTCDateTime(2013,08,01,10,0,0));
		$this->assertEquals(false, $event->validate());
	}
	
	function testFine() {
		$event = new EventModel();
		$event->setStartAt(getUTCDateTime(2013,08,01,10,0,0));
		$event->setEndAt(getUTCDateTime(2013,08,01,17,0,0));
		$this->assertEquals(true, $event->validate());
	}
	
	function testEndAfterStart() {
		$event = new EventModel();
		$event->setStartAt(getUTCDateTime(2013,08,01,18,0,0));
		$event->setEndAt(getUTCDateTime(2013,08,01,17,0,0));
		$this->assertEquals(false, $event->validate());
	}
}




