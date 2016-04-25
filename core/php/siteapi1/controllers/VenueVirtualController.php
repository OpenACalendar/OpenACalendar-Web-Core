<?php

namespace siteapi1\controllers;

use api1exportbuilders\EventListCSVBuilder;
use api1exportbuilders\ICalEventIdConfig;
use Silex\Application;
use site\forms\GroupNewForm;
use site\forms\GroupEditForm;
use site\forms\EventNewForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\VenueRepository;
use repositories\builders\GroupRepositoryBuilder;
use repositories\EventRepository;
use repositories\UserWatchesGroupRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
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
class VenueVirtualController {
	
	
	protected $parameters = array();
	
	
	function ical(Request $request, Application $app) {
		
		
		$ical = new EventListICalBuilder($app, $app['currentSite'], $app['currentTimeZone'], "Virtual Events", new ICalEventIdConfig($request->get('eventidconfig'), $request->server->all()));
		$ical->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json(Request $request, Application $app) {


		$ourRequest = new \Request($request);

		$json = new EventListJSONBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp(Request $request, Application $app) {

		$ourRequest = new \Request($request);
		
		$jsonp = new EventListJSONPBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	function csv(Request $request, Application $app) {

		$ourRequest = new \Request($request);

		$csv = new EventListCSVBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$csv->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$csv->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$csv->build();
		return $csv->getResponse();

	}

	function atomBefore(Request $request, Application $app) {
		
		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle('Virtual');
		$atom->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function atomCreate(Request $request, Application $app) {
		
		$atom = new EventListATOMCreateBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle('Virtual');
		$atom->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


