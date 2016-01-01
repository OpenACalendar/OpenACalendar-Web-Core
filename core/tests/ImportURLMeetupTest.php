<?php

use models\ImportModel;
use models\SiteModel;
use import\ImportMeetupHandler;
use import\ImportRun;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLMeetupTest extends \BaseAppTest {
	
	function dataForTestIsValid() {
		return array(
				array('http://madlab.org.uk/?ical',false,'xx'),
				array('http://www.meetup.com/HNLondon/',true,'http://www.meetup.com/HNLondon/events/ical/x/'),
				array('http://www.meetup.com/ORG-London/events/74016272/',true,'http://www.meetup.com/ORG-London/events/74016272/ical/x.ics'),
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
		
		
		$handler = new ImportMeetupHandler($this->app);
		$handler->setImportRun($importRun);
		$this->assertEquals($result, $handler->canHandle());
		if ($result) {
			$this->assertEquals($newURL, $handler->getNewFeedURL());
		}
	}
	
}


