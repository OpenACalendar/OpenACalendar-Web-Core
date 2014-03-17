<?php

use icalparser\ICalParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ICalParserTest  extends \PHPUnit_Framework_TestCase {
	
	function dataForTestTimeZone1() {
		return array(
				array('London.ical','Europe/London'),
				array('UTC.ical','UTC'),
				array('BasicICAL.ical','UTC'),
			);
	}
	
	/**
     * @dataProvider dataForTestTimeZone1
     */	
	function testTimeZone1 ($filename, $timeZone) {
		$parser = new ICalParser();
		$this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/".$filename));
		$this->assertEquals($timeZone, $parser->getTimeZoneIdentifier());
	}
	

	function dataForTestMultiLineDescription() {
		return array(
				array('IcalParserBasicImportMultiLineDescription.ical','Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Lizard'),
				array('IcalParserBasicImportMultiLineDescription2.ical','Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Lizard:Blue'),
			);
	}

	/**
     * @dataProvider dataForTestMultiLineDescription
     */		
	function testMultiLineDescription ($filename, $output) {
		$parser = new ICalParser();
		$this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/".$filename));
		$events = $parser->getEvents();
		$this->assertEquals(1, count($events));
		$event = $events[0];
		$this->assertEquals($output, $event->getDescription());
	}
	
	
	
}

