<?php


use models\ImportModel;
use models\SiteModel;
use import\ImportLanyrdHandler;
use import\ImportRun;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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
     *
     * @group import
     * @dataProvider dataForTestIsValid
     */	
	function testIsValid($currentYear, $url, $result, $newURL) {
		
		$this->app['timesource']->mock($currentYear);
		

		$import = new ImportModel();
		$import->setUrl($url);
		$site = new SiteModel();
		$importRun = new ImportRun($this->app, $import, $site);
		
		
		$handler = new ImportLanyrdHandler($this->app);
		$handler->setImportRun($importRun);
		$this->assertEquals($result, $handler->canHandle());
		if ($result) {
			$this->assertEquals($newURL, $handler->getNewFeedURL());
		}
	}
	
}


