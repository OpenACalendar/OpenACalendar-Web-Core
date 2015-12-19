<?php

namespace tests\models;

use models\EventModel;
use models\EventRecurSetModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelGetNewArbitraryEventTest extends \BaseAppTest {


	function dataForTestIsDateToSoonForArbitraryDate() {
		return array(
				array(2014,4,1,true),
				array(2015,4,1,false),
			);
	}

	/**
     * @dataProvider dataForTestIsDateToSoonForArbitraryDate
     */
	function testIsDateToSoonForArbitraryDate($year, $month, $day, $result) {

		$timeSource = new \TimeSource();
		$timeSource->mock(2015,03,01,10,0,0);

		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');

		$newDate = new \DateTime();
		$newDate->setDate($year, $month, $day);

		$this->assertEquals($result, $eventSet->isDateToSoonForArbitraryDate($newDate, $timeSource));
	}



	function dataForTestIsDateToLateForArbitraryDate() {
		return array(
				// test dates in past aren't to late
				array(2014,4,1,10,false),
				// real tests now
				array(2015,4,1,10,true),
				array(2015,4,1,40,false),
			);
	}

	/**
     * @dataProvider dataForTestIsDateToLateForArbitraryDate
     */
	function testIsDateToLateForArbitraryDate($year, $month, $day, $days, $result) {

		$timeSource = new \TimeSource();
		$timeSource->mock(2015,03,01,10,0,0);

		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');

		$newDate = new \DateTime();
		$newDate->setDate($year, $month, $day);

		$this->assertEquals($result, $eventSet->isDateToLateForArbitraryDate($newDate, $timeSource, $days));
	}



	
	
}

