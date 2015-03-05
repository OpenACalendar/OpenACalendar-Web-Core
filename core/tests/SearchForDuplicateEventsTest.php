<?php


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use \SearchForDuplicateEvents;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SearchForDuplicateEventsTest  extends \PHPUnit_Framework_TestCase {

	/** with venues **/
	function testScoreNoMatch1() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setUrl("http://www.greatevent.com");
		$eventNew->setVenueId(34);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setUrl("http://www.okevent.com");
		$eventNew->setVenueId(78);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(0, $score);
	}

	/** with areas **/
	function testScoreNoMatch2() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setUrl("http://www.greatevent.com");
		$eventNew->setAreaId(34);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setUrl("http://www.okevent.com");
		$eventNew->setAreaId(78);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(0, $score);
	}

	/** little info as possible **/
	function testScoreNoMatch3() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(0, $score);
	}


	function dataForTestScoreURLSame() {
		return array(
				array('http://www.greatevent.com','http://www.terribleevent.com',0),
				array('http://www.greatevent.com','http://www.greatevent.com',1),
				array('http://www.greatevent.com','http://www.GREATEVENT.com',1),
				array('http://www.greatevent.com','http://www.greatevent.com/',1),
				array('http://www.greatevent.com','http://www.GREATEVENT.com?',1),
				array('http://www.greatevent.com','http://www.greatevent.com/?',1),
				array('http://www.greatevent.com','https://www.greatevent.com',1),
				array('http://www.greatevent.com','HTTP://www.greatevent.com',1),
				// These tests are just to make sure the code catches the bad input fine
				array('greatevent','http://www.terribleevent.com',0),
				array('greatevent.com','http://www.terribleevent.com',0),
				array('greatevent.com/fantastic','http://www.terribleevent.com',0),
			);
	}


	/**
     * @dataProvider dataForTestScoreURLSame
     */
	function testScoreURLSame($url1, $url2, $score) {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setUrl($url1);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setUrl($url2);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		$this->assertEquals($score, $sfde->getScoreForConsideredEvent($eventExisting));
	}
	


	/**
	 * We're going to use the same data provider.
     * @dataProvider dataForTestScoreURLSame
     */
	function testScoreTicketURLSame($url1, $url2, $score) {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setTicketUrl($url1);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setTicketUrl($url2);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		$this->assertEquals($score, $sfde->getScoreForConsideredEvent($eventExisting));
	}

	function testScoreStartSame() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(1, $score);
	}

	function testScoreEndSame() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
			
		$this->assertEquals(1, $score);
	}
	

	function testScoreStartEndSame() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		// Only 1 of start or end matching counts as a point
		$this->assertEquals(1, $score);
	}
	

	function dataForTestScoreSummaryCompare() {
		return array(
				array('wibble','wobble',0),
				array('wibble wibble','wibble wibble', 1),
				array('wibble','wiBBle', 1),
				array('wibble cat dog','wiBBle', 1),
				array('wibble','cat wiBBle dof', 1),
				array('alpha beta','alphabeta', 0),
			);
	}
	
	/**
     * @dataProvider dataForTestScoreSummaryCompare
     */
	function testScoreSummaryCompare($summary1, $summary2, $scoreExpected) {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setSummary($summary1);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setSummary($summary2);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals($scoreExpected, $score);
	}	

	function testScoreVenueSame() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th April 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th April 2012 13:00:00"));
		$eventNew->setVenueId(34);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setVenueId(34);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(1, $score);
	}

	function testScoreAreaSame() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th April 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th April 2012 13:00:00"));
		$eventNew->setAreaId(34);
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setAreaId(34);
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(1, $score);
	}

	function testScoreAreaSameButByVenue() {
		$site = new SiteModel();

		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th April 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th April 2012 13:00:00"));
		$eventNew->setAreaId(34);

		$eventExisting = new models\EventModel();
		$eventExisting->setFromDataBaseRow(array(
			'id'=>1,
			'site_id'=>1,
			'slug'=>1,
			'summary'=>'Cat',
			'description'=>'Cat',
			'start_at'=>'2014:01:01 01:01:01',
			'end_at'=>'2014:01:01 01:01:01',
			'created_at'=>'2013:01:01 01:01:01',
			'is_deleted'=>0,
			'is_cancelled'=>0,
			'event_recur_set_id'=>null,
			'country_id'=>1,
			'venue_id'=>12,
			'area_id'=>null,
			'timezone'=>'Europe/London',
			'import_id'=>null,
			'import_url_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'area_information_id'=>34,
			'area_title'=>'Edinburgh',
			'area_slug'=>'1',
			'is_virtual'=>0,
			'is_physical'=>0,
			'is_duplicate_of_id'=>null,
		));

		$sfde = new SearchForDuplicateEvents($eventNew, $site);

		$score = $sfde->getScoreForConsideredEvent($eventExisting);

		$this->assertEquals(1, $score);
	}

	
}


