<?php


use models\ImportModel;
use models\SiteModel;
use import\ImportEventbriteHandler;
use import\ImportRun;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLEventbriteTest extends \BaseAppTest {
	
	function dataForTestIsValid() {
		return array(
				array('http://madlab.org.uk/?ical',false,'xx'),
				array('http://nengage5.eventbrite.com/',true,'http://www.eventbrite.com/calendar.ics?eid=4034956664&calendar=ical'),
				array('https://nengage5.eventbrite.com/',true,'http://www.eventbrite.com/calendar.ics?eid=4034956664&calendar=ical'),
				array('http://www.eventbrite.co.uk/event/4787315991/',true,'http://www.eventbrite.co.uk/calendar.ics?eid=4787315991&calendar=ical'),
				array('http://www.eventbrite.co.uk/event/4787315991/?ref=enivtefor001&invite=MjcyNjcyMS9qYW1lc0BqYXJvZmdyZWVuLmNvLnVrLzA%3D&utm_source=eb_email&utm_medium=email&utm_campaign=inviteformal001&utm_term=eventpage&ebtv=C',true,'http://www.eventbrite.co.uk/calendar.ics?eid=4787315991&calendar=ical'),
				array('https://www.eventbrite.co.uk/e/the-venue-expo-tickets-7423912121?aff=ehomecard&rank=2',true,'http://www.eventbrite.co.uk/calendar.ics?eid=7423912121&calendar=ical'),
			);
	}
	
	/**
     * @dataProvider dataForTestIsValid
     */	
	function testIsValid($url, $result, $newURL) {

		$import = new ImportModel();
		$import->setUrl($url);
		$site = new SiteModel();
		$importRun = new ImportRun($import, $site);
		
		
		$handler = new ImportEventbriteHandler();
		$handler->setImportRun($importRun);
		$this->assertEquals($result, $handler->canHandle());
		if ($result) {
			$this->assertEquals($newURL, $handler->getNewFeedURL());
		}
	}
	
}


