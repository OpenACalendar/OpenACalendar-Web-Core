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

	function testScoreZero() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setUrl("http://www.greatevent.com");
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setUrl("http://www.okevent.com");
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(0, $score);
	}

	function testScoreURLSame() {
		$site = new SiteModel();
		
		$eventNew = new models\EventModel();
		$eventNew->setStartAt(new \DateTime("12th Feb 2012 10:00:00"));
		$eventNew->setEndAt(new \DateTime("12th Feb 2012 13:00:00"));
		$eventNew->setUrl("http://www.greatevent.com");
		
		$eventExisting = new models\EventModel();
		$eventExisting->setStartAt(new \DateTime("12th March 2012 10:00:00"));
		$eventExisting->setEndAt(new \DateTime("12th March 2012 13:00:00"));
		$eventExisting->setUrl("http://www.greatevent.com");
		
		$sfde = new SearchForDuplicateEvents($eventNew, $site);
		
		$score = $sfde->getScoreForConsideredEvent($eventExisting);
		
		$this->assertEquals(1, $score);
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
		
		$this->assertEquals(2, $score);
	}
	
	
	
	
}


