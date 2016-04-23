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
use repositories\GroupRepository;
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
class GroupController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();

		if (strpos($slug,"-") > 0) {
			$slugBits = explode("-", $slug, 2);
			$slug = $slugBits[0];
		}

		$gr = new GroupRepository($app);
		$this->parameters['group'] = $gr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['group']) {
			return false;
		}
		
		return true;
	}
	
	
	function ical($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		$ical = new EventListICalBuilder($app, $app['currentSite'], $app['currentTimeZone'],$this->parameters['group']->getTitle(), new ICalEventIdConfig($request->get('eventidconfig'), $request->server->all()));
		$ical->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		
		$json = new EventListJSONBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	
	function csv($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		$csv = new EventListCSVBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$csv->setTitle($this->parameters['group']->getTitle());
		$csv->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$csv->build();
		return $csv->getResponse();
	}	

	function atomBefore($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle($this->parameters['group']->getTitle());
		$atom->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$atom->build();
		return $atom->getResponse();
	}


	function atomCreate($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		
		$atom = new EventListATOMCreateBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle($this->parameters['group']->getTitle());
		$atom->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


