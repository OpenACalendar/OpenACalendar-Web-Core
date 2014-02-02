<?php

use models\ImportURLModel;
use models\SiteModel;
use import\ImportURLNotUsHandler;
use import\ImportURLRun;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLNotUsTest  extends \PHPUnit_Framework_TestCase  {

	
	function dataForTestIsValid() {
		return array(
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://madlab.org.uk/?ical',false),
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://opentechcalendar.co.uk/index.php/event/ical/',false),
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://demo.hasacalendar.co.uk/index.php/event/ical/',true),
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://ican.hasacalendar.co.uk/index.php/event/ical/',true),
			);
	}
	
	/**
     * @dataProvider dataForTestIsValid
     */	
	function testIsValid($webIndexDomain, $webSiteDomain, $hasSSL, $webSiteDomain, $webSiteDomainSSL, $url, $result) {
		global $CONFIG;
		
		$CONFIG->webIndexDomain = $webIndexDomain;
		$CONFIG->webSiteDomain = $webSiteDomain;
		$CONFIG->hasSSL = $hasSSL;
		$CONFIG->webSiteDomain = $webSiteDomain;
		$CONFIG->webSiteDomainSSL = $webSiteDomainSSL;
				
		$import = new ImportURLModel();
		$import->setUrl($url);
		$site = new SiteModel();
		$importRun = new ImportURLRun($import, $site);
		
		$handler = new ImportURLNotUsHandler($importRun);
		$this->assertEquals($result, $handler->canHandle());
	}
	
}


