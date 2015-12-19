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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLNotUsTest  extends \BaseAppTest  {

	
	function dataForTestIsValid() {
		return array(
				# without ports
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://madlab.org.uk/?ical',false),
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://opentechcalendar.co.uk/index.php/event/ical/',false),
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://demo.hasacalendar.co.uk/index.php/event/ical/',true),
				array('ican.hasacalendar.co.uk','hasacalendar.co.uk',true,'ican.hasacalendar.co.uk','hasacalendar.co.uk',
					'http://ican.hasacalendar.co.uk/index.php/event/ical/',true),
				# With ports
				array('hasadevcalendar.co.uk:20150','hasadevcalendar.co.uk:20151',true,'hasadevcalendar.co.uk:40300','hasadevcalendar.co.uk:40302',
					'http://www.facebook.com/events/1435905404p890489089045',false),
				array('hasadevcalendar.co.uk:20150','hasadevcalendar.co.uk:20151',true,'hasadevcalendar.co.uk:40300','hasadevcalendar.co.uk:40302',
					'http://test1.hhasadevcalendar.co.uk:20151/index.php/event/ical/',true),
			);
	}
	
	/**
     * @dataProvider dataForTestIsValid
     */	
	function testIsValid($webIndexDomain, $webSiteDomain, $hasSSL, $webIndexDomainSSL, $webSiteDomainSSL, $url, $result) {
		global $CONFIG;
		
		$CONFIG->webIndexDomain = $webIndexDomain;
		$CONFIG->webSiteDomain = $webSiteDomain;
		$CONFIG->hasSSL = $hasSSL;
		$CONFIG->webIndexDomainSSL = $webIndexDomainSSL;
		$CONFIG->webSiteDomainSSL = $webSiteDomainSSL;
				
		$import = new ImportURLModel();
		$import->setUrl($url);
		$site = new SiteModel();
		$importRun = new ImportURLRun($import, $site);
		
		$handler = new ImportURLNotUsHandler();
		$handler->setImportURLRun($importRun);
		$this->assertEquals($result, $handler->canHandle());
	}
	
}


