<?php

namespace JMBTechnologyLimited\ParseDateTimeRangeString;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ParseDateTimeRangeString
 * @license https://raw.github.com/JMB-Technology-Limited/ParseDateTimeRangeString/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseDateTimeRangeStringStartEndTest extends \PHPUnit_Framework_TestCase{

	
	function startEndProvider() {
		return array(
			// if no end time, 2 hours default
			array('15th dec 2013 7pm',  2013,12,15,19,0,  2013,12,15,21,0),
			array('15th dec 2013 7pm to 10pm',  2013,12,15,19,0,  2013,12,15,22,0),
			array('mon 7am to tue 10pm',  2013,10,7,7,0,  2013,10,8,22,0),
			array('mon 7am to 10pm',  2013,10,7,7,0,  2013,10,7,22,0),
			array('mon 7am-10pm',  2013,10,7,7,0,  2013,10,7,22,0),
			array('mon 7-10pm',  2013,10,7,19,0,  2013,10,7,22,0),
			// specify end by duration ...
			// ... hours
			array('mon 7pm one hour',  2013,10,7,19,0,  2013,10,7,20,0),
			array('mon 7pm 1 hour',  2013,10,7,19,0,  2013,10,7,20,0),
			array('mon 7pm 1hr',  2013,10,7,19,0,  2013,10,7,20,0),
			array('mon 7pm two hours',  2013,10,7,19,0,  2013,10,7,21,0),
			array('mon 7pm 2 hours',  2013,10,7,19,0,  2013,10,7,21,0),
			array('mon 7pm three hours',  2013,10,7,19,0,  2013,10,7,22,0),
			array('mon 7pm 3 hours',  2013,10,7,19,0,  2013,10,7,22,0),
			array('mon 7pm four hours',  2013,10,7,19,0,  2013,10,7,23,0),
			array('mon 7pm 4 hours',  2013,10,7,19,0,  2013,10,7,23,0),
			array('mon 7pm five hours',  2013,10,7,19,0,  2013,10,8,0,0),
			array('mon 7pm 5 hours',  2013,10,7,19,0,  2013,10,8,0,0),
			array('mon 7pm six hours',  2013,10,7,19,0,  2013,10,8,1,0),
			array('mon 7pm 6 hours',  2013,10,7,19,0,  2013,10,8,1,0),
			array('mon 7pm seven hours',  2013,10,7,19,0,  2013,10,8,2,0),
			array('mon 7pm 7 hours',  2013,10,7,19,0,  2013,10,8,2,0),
			// ... hours & mins
			array('mon 7pm one hour 30 mins',  2013,10,7,19,0,  2013,10,7,20,30),
			array('mon 7pm 1 hour 30 mins',  2013,10,7,19,0,  2013,10,7,20,30),
			array('mon 7pm 120 mins',  2013,10,7,19,0,  2013,10,7,21,00),
			array('mon 7pm 30 mins',  2013,10,7,19,0,  2013,10,7,19,30),
			array('mon 7pm thirty mins',  2013,10,7,19,0,  2013,10,7,19,30),
			// ... hours
			array('mon 7pm for one hour',  2013,10,7,19,0,  2013,10,7,20,0),
			array('mon 7pm for two hours',  2013,10,7,19,0,  2013,10,7,21,0),
			array('mon 7pm for 2 hours',  2013,10,7,19,0,  2013,10,7,21,0),
			array('mon 7pm for three hours',  2013,10,7,19,0,  2013,10,7,22,0),
			array('mon 7pm for 3 hours',  2013,10,7,19,0,  2013,10,7,22,0),
			array('mon 7pm for four hours',  2013,10,7,19,0,  2013,10,7,23,0),
			array('mon 7pm for 4 hours',  2013,10,7,19,0,  2013,10,7,23,0),
			array('mon 7pm for five hours',  2013,10,7,19,0,  2013,10,8,0,0),
			array('mon 7pm for 5 hours',  2013,10,7,19,0,  2013,10,8,0,0),
			array('mon 7pm for six hours',  2013,10,7,19,0,  2013,10,8,1,0),
			array('mon 7pm for 6 hours',  2013,10,7,19,0,  2013,10,8,1,0),
			array('mon 7pm for seven hours',  2013,10,7,19,0,  2013,10,8,2,0),
			array('mon 7pm for 7 hours',  2013,10,7,19,0,  2013,10,8,2,0),
			// ... hours & mins
			array('mon 7pm for one hour 30 mins',  2013,10,7,19,0,  2013,10,7,20,30),
			array('mon 7pm for 1 hour 30 mins',  2013,10,7,19,0,  2013,10,7,20,30),
			array('mon 7pm for 120 mins',  2013,10,7,19,0,  2013,10,7,21,00),
			array('mon 7pm for 30 mins',  2013,10,7,19,0,  2013,10,7,19,30),
			array('mon 7pm for thirty mins',  2013,10,7,19,0,  2013,10,7,19,30),
			// days, no time
			array('15th dec to 17th dec',  2013,12,15,0,0,  2013,12,17,23,59),
			// between and
			array('between mon 7pm and 10pm',  2013,10,7,19,0,  2013,10,7,22,0),
			array('friday between 7pm and 10pm',  2013,10,4,19,0,  2013,10,4,22,0),
			// from Al.
			array('20th Dec 2014 1800 to 1900',  2014,12,20,18,0,  2014,12,20,19,0),
		);
	}
	
	/**
	* @dataProvider startEndProvider
	*/ 
	function testStartEnd($stringIn, $syear, $smonth, $sday, $shour, $sminute, $eyear, $emonth, $eday, $ehour, $eminute) {
		$dt = new \DateTime;
		$dt->setTimezone(new \DateTimeZone("Europe/London"));
		$dt->setDate(2013, 10, 1);
		$dt->setTime(13, 0, 0);
		$parse = new ParseDateTimeRangeString($dt, "Europe/London");
		$result = $parse->parse($stringIn);		
		$this->assertFalse(is_null($result->getStart()));
		$this->assertEquals($syear, $result->getStart()->format('Y'));
		$this->assertEquals($smonth, $result->getStart()->format('n'));
		$this->assertEquals($sday, $result->getStart()->format('j'));
		$this->assertEquals($shour, $result->getStart()->format('G'));
		$this->assertEquals($sminute, $result->getStart()->format('i'));
		$this->assertEquals($eyear, $result->getEnd()->format('Y'));
		$this->assertEquals($emonth, $result->getEnd()->format('n'));
		$this->assertEquals($eday, $result->getEnd()->format('j'));
		$this->assertEquals($ehour, $result->getEnd()->format('G'));
		$this->assertEquals($eminute, $result->getEnd()->format('i'));
	}
	
	
	
}

