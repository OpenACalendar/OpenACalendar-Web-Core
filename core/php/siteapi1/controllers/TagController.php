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
use repositories\TagRepository;
use repositories\builders\TagRepositoryBuilder;
use repositories\EventRepository;
use repositories\UserWatchesTagRepository;
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
class TagController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();
		
		$tr = new TagRepository();
		$this->parameters['tag'] = $tr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['tag']) {
			return false;
		}
		
		return true;
	}
	
	
	function ical($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}
		
		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'],$this->parameters['tag']->getTitle());
		$ical->getEventRepositoryBuilder()->setTag($this->parameters['tag']);
		$ical->build();
		return $ical->getResponse();
				
	}

	function json($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}

		
		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setTag($this->parameters['tag']);
		$json->build();
		return $json->getResponse();
				
	}	

	function jsonp($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setTag($this->parameters['tag']);
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	

	
	function atomBefore($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}

		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle($this->parameters['tag']->getTitle());
		$atom->getEventRepositoryBuilder()->setTag($this->parameters['tag']);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function atomCreate($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}

		
		$atom = new EventListATOMCreateBuilder($app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle($this->parameters['tag']->getTitle());
		$atom->getEventRepositoryBuilder()->setTag($this->parameters['tag']);
		$atom->build();
		return $atom->getResponse();
	}	
	
	
}


