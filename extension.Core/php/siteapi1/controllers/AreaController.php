<?php

namespace siteapi1\controllers;

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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();
		
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
		
		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'],$this->parameters['area']->getTitle());
		$ical->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		
		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	
	function atomBefore($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app['currentSite'], $app['currentTimeZone']);
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

		
		$atom = new EventListATOMCreateBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle($this->parameters['area']->getTitle());
		$atom->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


