<?php

use models\EventHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\EventHistoryRepository;
use repositories\CountryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RenderCalendarTest extends \PHPUnit_Framework_TestCase {
	
	
	function dataForTestByDate() {
		return array(
				array(2014,4,28,  31,true,  2014,4,28,  2014,6,1,  2014,5),
				array(2014,4,9,  31,true,  2014,4,7,  2014,5,11,  2014,4),
			);
	}
	
	/**
     * @dataProvider dataForTestByDate
     */
	function testByDate($inYear, $inMonth, $inDay, $days, $expand, $startYear, $startMonth, $startDate, $endYear, $endMonth, $endDate, $outYear, $outMonth) {
		$cal = new RenderCalendar();
		
		$inDate = new \DateTime();
		$inDate->setDate($inYear, $inMonth, $inDay);
		$cal->byDate($inDate, $days, $expand);
		
		$start = $cal->getStart();
		$this->assertEquals($startYear, intval($start->format("Y")));
		$this->assertEquals($startMonth, intval($start->format("n")));
		$this->assertEquals($startDate, intval($start->format("j")));

		$end = $cal->getEnd();
		$this->assertEquals($endYear, intval($end->format("Y")));
		$this->assertEquals($endMonth, intval($end->format("n")));
		$this->assertEquals($endDate, intval($end->format("j")));

		$this->assertEquals($outYear, $cal->getYear());
		$this->assertEquals($outMonth, $cal->getMonth());
	}
	
}
