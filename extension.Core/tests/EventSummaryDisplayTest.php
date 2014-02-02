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
class EventSummaryDisplayTest  extends \PHPUnit_Framework_TestCase {
	
	
	function dataForTestWithGroup() {
		return array(
				array('My group','Monthly meetup','My group: Monthly meetup'),
				array('My group','','My group'),
				array('EDLUG','EDLUG','EDLUG'),
				array('Monthly meetup','Monthly meetup','Monthly meetup'),
				array('BCS Glasgow','BCS Glasgow Branch Meeting - Optimising virtual keyboards','BCS Glasgow Branch Meeting - Optimising virtual keyboards'),
			);
	}
			
		
	/**
     * @dataProvider dataForTestWithGroup
     */	
	function testWithGroup( $groupTitle, $eventSummary, $summaryDisplayOut) {		
		$event = new EventModel();
		$event->setSummary($eventSummary);
		$event->setGroupTitle($groupTitle);
		$this->assertEquals($summaryDisplayOut, $event->getSummaryDisplay());
	}
	
}