<?php

namespace siteapi1\controllers;

use api1exportbuilders\EventListCSVBuilder;
use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;
use api1exportbuilders\EventListICalBuilder;
use api1exportbuilders\EventListJSONBuilder;
use api1exportbuilders\EventListJSONPBuilder;
use api1exportbuilders\EventListATOMBeforeBuilder;
use api1exportbuilders\EventListATOMCreateBuilder;
use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListController {
	
	
	function ical(Application $app) {
		
		$ical = new EventListICalBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$ical->build();
		return $ical->getResponse();
			
	}

	function json(Request $request, Application $app) {

		$ourRequest = new \Request($request);

		$json = new EventListJSONBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->build();
		return $json->getResponse();
			
	}

	function csv(Request $request, Application $app) {

		$ourRequest = new \Request($request);

		$csv = new EventListCSVBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$csv->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$csv->build();
		return $csv->getResponse();

	}

	function jsonp(Request $request, Application $app) {

		$ourRequest = new \Request($request);

		$jsonp = new EventListJSONPBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
			
	}
	
	
	function atomBefore(Request $request, Application $app) {
		
		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function atomCreate(Request $request, Application $app) {
		
		$atom = new EventListATOMCreateBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


