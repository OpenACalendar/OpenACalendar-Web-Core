<?php

namespace org\openacalendar\curatedlists\siteapi1\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use org\openacalendar\curatedlists\repositories\CuratedListRepository;
use api1exportbuilders\EventListICalBuilder;
use api1exportbuilders\EventListJSONBuilder;
use api1exportbuilders\EventListJSONPBuilder;
use api1exportbuilders\EventListATOMBeforeBuilder;
use api1exportbuilders\EventListATOMCreateBuilder;

use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();

		if (strpos($slug,"-") > 0) {
			$slugBits = explode("-", $slug, 2);
			$slug = $slugBits[0];
		}

		$clr = new CuratedListRepository();
		$this->parameters['curatedlist'] = $clr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['curatedlist']) {
			return false;
		}
		
		return true;
	}
	
	
	function ical($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}
		
		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'], $this->parameters['curatedlist']->getTitle());
		$ical->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		
		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	function atomBefore($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle($this->parameters['curatedlist']->getTitle());
		$atom->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function atomCreate($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		
		$atom = new EventListATOMCreateBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle($this->parameters['curatedlist']->getTitle());
		$atom->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


