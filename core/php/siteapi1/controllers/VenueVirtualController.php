<?php

namespace siteapi1\controllers;

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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueVirtualController {
	
	
	protected $parameters = array();
	
	
	function ical(Request $request, Application $app) {
		
		
		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'], "Virtual Events");
		$ical->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json(Request $request, Application $app) {
		
		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp(Request $request, Application $app) {
		
		
		$jsonp = new EventListJSONPBuilder($app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	function atomBefore(Request $request, Application $app) {
		
		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle('Virtual');
		$atom->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function atomCreate(Request $request, Application $app) {
		
		$atom = new EventListATOMCreateBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle('Virtual');
		$atom->getEventRepositoryBuilder()->setVenueVirtualOnly(true);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


