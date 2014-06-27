<?php


use models\ImportURLModel;
use models\SiteModel;
use import\ImportURLEventbriteHandler;
use import\ImportURLRun;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLEventbriteTest extends \PHPUnit_Framework_TestCase {
	
	function dataForTestIsValid() {
		return array(
				array('http://madlab.org.uk/?ical',false,'xx'),
				array('http://nengage5.eventbrite.com/',true,'http://www.eventbrite.com/calendar.ics?eid=4034956664&calendar=ical'),
				array('https://nengage5.eventbrite.com/',true,'http://www.eventbrite.com/calendar.ics?eid=4034956664&calendar=ical'),
				array('http://www.eventbrite.co.uk/event/4787315991/',true,'http://www.eventbrite.co.uk/calendar.ics?eid=4787315991&calendar=ical'),
				array('http://www.eventbrite.co.uk/event/4787315991/?ref=enivtefor001&invite=MjcyNjcyMS9qYW1lc0BqYXJvZmdyZWVuLmNvLnVrLzA%3D&utm_source=eb_email&utm_medium=email&utm_campaign=inviteformal001&utm_term=eventpage&ebtv=C',true,'http://www.eventbrite.co.uk/calendar.ics?eid=4787315991&calendar=ical'),
			);
	}
	
	/**
     * @dataProvider dataForTestIsValid
     */	
	function testIsValid($url, $result, $newURL) {

		$import = new ImportURLModel();
		$import->setUrl($url);
		$site = new SiteModel();
		$importRun = new ImportURLRun($import, $site);
		
		
		$handler = new ImportURLEventbriteHandler();
		$handler->setImportURLRun($importRun);
		$this->assertEquals($result, $handler->canHandle());
		if ($result) {
			$this->assertEquals($newURL, $handler->getNewFeedURL());
		}
	}
	
}


