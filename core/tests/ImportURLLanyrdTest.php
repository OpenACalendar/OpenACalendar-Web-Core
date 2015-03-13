<?php


use models\ImportURLModel;
use models\SiteModel;
use import\ImportURLLanyrdHandler;
use import\ImportURLRun;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLLanyrdTest extends \BaseAppTest {
	
	function dataForTestIsValid() {
		return array(
				array(2012,'http://madlab.org.uk/?ical',false,'xx'),
				array(2012,'http://lanyrd.com/2012/asyncjs-hypermedia/',true,'http://lanyrd.com/2012/asyncjs-hypermedia/asyncjs-hypermedia.ics'),
			);
	}
	
	/**
     * @dataProvider dataForTestIsValid
     */	
	function testIsValid($currentYear, $url, $result, $newURL) {
		
		\TimeSource::mock($currentYear);
		

		$import = new ImportURLModel();
		$import->setUrl($url);
		$site = new SiteModel();
		$importRun = new ImportURLRun($import, $site);
		
		
		$handler = new ImportURLLanyrdHandler();
		$handler->setImportURLRun($importRun);
		$this->assertEquals($result, $handler->canHandle());
		if ($result) {
			$this->assertEquals($newURL, $handler->getNewFeedURL());
		}
	}
	
}


