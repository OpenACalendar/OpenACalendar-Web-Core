<?php

namespace JMBTechnologyLimited\ParseDateTimeRangeString;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ParseDateTimeRangeString
 * @license https://raw.github.com/JMB-Technology-Limited/ParseDateTimeRangeString/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseDateTimeRangeStringTest extends \PHPUnit_Framework_TestCase{

	
	function startProvider() {
		return array(
			array('15th dec 2013 7pm',2013,12,15,19,0),
			array('2013 15th dec 7pm',2013,12,15,19,0),
			array('2013 dec 15th 7pm',2013,12,15,19,0),
			array('15th dec 7pm',2013,12,15,19,0),
			array('dec 15th 7pm',2013,12,15,19,0),
			array('15th 7pm',2013,10,15,19,0),
			array('today 7pm',2013,10,1,19,0),
			array('toady 7pm',2013,10,1,19,0),
			array('tomorrow 7pm',2013,10,2,19,0),
			array('tomorow 7pm',2013,10,2,19,0),
			array('tommorrow 7pm',2013,10,2,19,0),
			array('tomorrow noon',2013,10,2,12,0),
			array('tomorrow midnight',2013,10,3,0,0),
			array('wed 7pm',2013,10,2,19,0),
			array('thu 7pm',2013,10,3,19,0),
			array('sat 7pm',2013,10,5,19,0),
			array('sun 7pm',2013,10,6,19,0),
			array('mon 7pm',2013,10,7,19,0),
			array('mon 8:12am',2013,10,7,8,12),
			array('mon 8:12pm',2013,10,7,20,12),
			array('mon 8:05am',2013,10,7,8,5),
			array('mon 8:05pm',2013,10,7,20,5),
			array('mon 8:5am',2013,10,7,8,5),
			array('mon 8:5pm',2013,10,7,20,5),
			array('next tue 7pm',2013,10,8,19,0),
			array('next wed 7pm',2013,10,9,19,0),
			array('next thu 7pm',2013,10,10,19,0),
			array('next fri 7pm',2013,10,11,19,0),
			array('next sat 7pm',2013,10,12,19,0),
			array('next sun 7pm',2013,10,13,19,0),
			array('wednesday 7pm',2013,10,2,19,0),
			array('thursday 7pm',2013,10,3,19,0),
			array('saturday 7pm',2013,10,5,19,0),
			array('sunday 7pm',2013,10,6,19,0),
			array('monday 7pm',2013,10,7,19,0),
			array('friday 12:30am',2013,10,4,0,30),
			array('next tuesday 7pm',2013,10,8,19,0),
			array('next wednesday 7pm',2013,10,9,19,0),
			array('next thursday 7pm',2013,10,10,19,0),
			array('next friday 7pm',2013,10,11,19,0),
			array('next saturday 7pm',2013,10,12,19,0),
			array('next sunday 7pm',2013,10,13,19,0),
			array('7pm',2013,10,1,19,0),
			array('9pm',2013,10,1,21,0),
			array('21:00',2013,10,1,21,0),
			array('21:05',2013,10,1,21,5),
			array('21:5',2013,10,1,21,5),
			array('21:25',2013,10,1,21,25),
			array('2100',2013,10,1,21,0),
			array('2105',2013,10,1,21,5),
			array('2125',2013,10,1,21,25),
			array('21.00',2013,10,1,21,0),
			array('21.05',2013,10,1,21,5),
			array('21.5',2013,10,1,21,5),
			array('21.25',2013,10,1,21,25),
			array('tomorrow 9:00',2013,10,2,9,0),
			array('tomorrow 09:00',2013,10,2,9,0),
			array('tomorrow 9.00',2013,10,2,9,0),
			array('tomorrow 09.00',2013,10,2,9,0),
			array('tomorrow 0:30',2013,10,2,0,30),
			array('tomorrow 0.30',2013,10,2,0,30),
			// test: 12am & 12pm
			array('tomorrow 12am',2013,10,2,0,0),
			array('tomorrow 12pm',2013,10,2,12,00),
			array('tomorrow 12:30am',2013,10,2,0,30),
			array('tomorrow 12:30pm',2013,10,2,12,30),
			// test: this day on the xth week of the month
			array('1st wednesday nov 09:00',2013,11,6,9,0),
			array('First wednesday nov 09:00',2013,11,6,9,0),
			array('2nd wed nov 09:00',2013,11,13,9,0),
			array('Second wed nov 09:00',2013,11,13,9,0),
			array('3rd wednesday nov 09:00',2013,11,20,9,0),
			array('Third wednesday nov 09:00',2013,11,20,9,0),
			array('last wednesday nov 09:00',2013,11,27,9,0),
			array('1st sunday dec 09:00',2013,12,1,9,0),
			array('2nd sunday dec 09:00',2013,12,8,9,0),
			array('3rd  sunday 09:00',2013,10,20,9,0),
			array('4th  sunday 09:00',2013,10,27,9,0),
			array('fourth  sunday 09:00',2013,10,27,9,0),
			array('last sun dec 09:00',2013,12,29,9,0),
			// Test: years
			array('1st jan 2014 09:00',2014,1,1,9,0),
			array('1st jan next year 09:00',2014,1,1,9,0),
			array('1st jan this year 09:00',2013,1,1,9,0),
			// if a day of week and time in past ... next week
			array('tuesday 17:00',2013,10,1,17,0),
			array('tuesday 10:00',2013,10,8,10,0),
			// parse short date formats, full
			array('2013/12/15 10:00',2013,12,15,10,0),
			array('15/12/2013 10:00',2013,12,15,10,0),
			array('12/15/2013 10:00',2013,12,15,10,0),
			array('10/12/2013 10:00',2013,12,10,10,0),
			array('15/12/13 10:00',2013,12,15,10,0),
			array('12/15/13 10:00',2013,12,15,10,0),
			array('10/12/13 10:00',2013,12,10,10,0),
			// day is single digit
			array('2013/12/2 10:00',2013,12,2,10,0),
			array('2/12/2013 10:00',2013,12,2,10,0),
			array('2/12/13 10:00',2013,12,2,10,0),
			// month is single digit
			array('20/2/14 10:00',2014,2,20,10,0),
			array('2/20/14 10:00',2014,2,20,10,0),
			array('2/20/2014 10:00',2014,2,20,10,0),
			array('20/2/2014 10:00',2014,2,20,10,0),
			array('2014/20/2 10:00',2014,2,20,10,0),
			array('2014/2/20 10:00',2014,2,20,10,0),
			// from Jon.
			array('Thursday, 19 December 2013 from 13:00 to 18:00',2013,12,19,13,0),
		);
	}
	
	/**
	* @dataProvider startProvider
	*/ 
	function testStart($stringIn, $year, $month, $day, $hour, $minute) {
		$dt = new \DateTime;
		$dt->setTimezone(new \DateTimeZone("Europe/London"));
		$dt->setDate(2013, 10, 1);
		$dt->setTime(13, 0, 0);
		$parse = new ParseDateTimeRangeString($dt, "Europe/London");
		$result = $parse->parse($stringIn);
		$this->assertFalse(is_null($result->getStart()));
		$this->assertEquals($year, $result->getStart()->format('Y'));
		$this->assertEquals($month, $result->getStart()->format('n'));
		$this->assertEquals($day, $result->getStart()->format('j'));
		$this->assertEquals($hour, $result->getStart()->format('G'));
		$this->assertEquals($minute, $result->getStart()->format('i'));
	}
	
	function startProvider2() {
		return array(
			// specify both day and date.
			array('tue 3rd dec 9am',2013,12,3,9,0),
		);
	}
	
	/**
	 * Some tests needed a different start date!
	* @dataProvider startProvider2
	*/ 
	function testStart2($stringIn, $year, $month, $day, $hour, $minute) {
		$dt = new \DateTime;
		$dt->setTimezone(new \DateTimeZone("Europe/London"));
		$dt->setDate(2013, 11, 18);
		$dt->setTime(13, 0, 0);
		$parse = new ParseDateTimeRangeString($dt, "Europe/London");
		$result = $parse->parse($stringIn);
		$this->assertFalse(is_null($result->getStart()));
		$this->assertEquals($year, $result->getStart()->format('Y'));
		$this->assertEquals($month, $result->getStart()->format('n'));
		$this->assertEquals($day, $result->getStart()->format('j'));
		$this->assertEquals($hour, $result->getStart()->format('G'));
		$this->assertEquals($minute, $result->getStart()->format('i'));
	}
	
	
}

