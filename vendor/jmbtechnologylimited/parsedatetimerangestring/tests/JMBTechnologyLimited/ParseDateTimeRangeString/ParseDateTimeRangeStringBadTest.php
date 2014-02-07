<?php

namespace JMBTechnologyLimited\ParseDateTimeRangeString;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ParseDateTimeRangeString
 * @license https://raw.github.com/JMB-Technology-Limited/ParseDateTimeRangeString/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseDateTimeRangeStringBadTest extends \PHPUnit_Framework_TestCase{
	
	function noCrashOnBadDatesProvider() {
		return array(
			array('2014 30th feb'),
			array('2014/2/30'),
			array('2013/13/13'),
			array('13/13/2013'),
			array('13/13/13'),
			array('5th Thu Nov'),
		);
	}
	
	/**
	 * We just want to make sure we get a date back here, and the code doesn't crash
	* @dataProvider noCrashOnBadDatesProvider
	*/ 
	function testNoCrashOnBadDates($stringIn) {
		$dt = new \DateTime;
		$dt->setTimezone(new \DateTimeZone("Europe/London"));
		$dt->setDate(2013, 10, 1);
		$dt->setTime(13, 0, 0);
		$parse = new ParseDateTimeRangeString($dt, "Europe/London");
		$result = $parse->parse($stringIn);
		$this->assertFalse(is_null($result->getStart()));
	}
	
	
}

