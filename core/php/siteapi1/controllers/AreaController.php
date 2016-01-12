<?php

namespace siteapi1\controllers;

use api1exportbuilders\EventListCSVBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\AreaRepository;
use api1exportbuilders\EventListICalBuilder;
use api1exportbuilders\EventListJSONBuilder;
use api1exportbuilders\EventListJSONPBuilder;
use api1exportbuilders\EventListATOMBeforeBuilder;
use api1exportbuilders\EventListATOMCreateBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();

		if (strpos($slug,"-") > 0) {
			$slugBits = explode("-", $slug, 2);
			$slug = $slugBits[0];
		}

		$ar = new AreaRepository();
		$this->parameters['area'] = $ar->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['area']) {
			return false;
		}
		
		return true;
	}
	
	
	function ical($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}
		
		$ical = new EventListICalBuilder($app, $app['currentSite'], $app['currentTimeZone'],$this->parameters['area']->getTitle());
		$ical->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		
		$json = new EventListJSONBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	function csv($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}


		$csv = new EventListCSVBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$csv->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$csv->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$csv->build();
		return $csv->getResponse();

	}

	
	function atomBefore($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle($this->parameters['area']->getTitle());
		$atom->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function atomCreate($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		
		$atom = new EventListATOMCreateBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle($this->parameters['area']->getTitle());
		$atom->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


