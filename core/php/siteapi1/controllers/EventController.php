<?php

namespace siteapi1\controllers;

use repositories\builders\MediaRepositoryBuilder;
use Silex\Application;
use site\forms\EventNewForm;
use site\forms\EventEditForm;
use site\forms\EventDeleteForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\SiteModel;
use models\EventModel;
use models\EventRecurSetModel;
use repositories\EventRepository;
use repositories\EventHistoryRepository;
use repositories\GroupRepository;
use repositories\VenueRepository;
use repositories\AreaRepository;
use repositories\CountryRepository;
use repositories\EventRecurSetRepository;
use repositories\UserAtEventRepository;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\EventHistoryRepositoryBuilder;
use api1exportbuilders\EventListICalBuilder;
use api1exportbuilders\EventListJSONBuilder;
use api1exportbuilders\EventListJSONPBuilder;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController {
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array('groups'=>array(),'country'=>null,'venue'=>null,'area'=>null);

		if (strpos($slug,"-") > 0) {
			$slugBits = explode("-", $slug, 2);
			$slug = $slugBits[0];
		}

		$eventRepository = new EventRepository($app);
		$this->parameters['event'] =  $eventRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['event']) {
			return false;
		}

		if ($this->parameters['event']->getGroupId()) {
			$grb = new GroupRepositoryBuilder($app);
			$grb->setEvent($this->parameters['event']);
			$this->parameters['groups'] = $grb->fetchAll();
		}
		
		if ($this->parameters['event']->getVenueID()) {
			$vr = new VenueRepository($app);
			$this->parameters['venue'] = $vr->loadById($this->parameters['event']->getVenueID());
		}
		
		if ($this->parameters['event']->getAreaID()) {
			$ar = new AreaRepository($app);
			$this->parameters['area'] = $ar->loadById($this->parameters['event']->getAreaID());
		} elseif ($this->parameters['venue'] && $this->parameters['venue']->getAreaId()) {
			$ar = new AreaRepository($app);
			$this->parameters['area'] = $ar->loadById($this->parameters['venue']->getAreaID());
		}
		
		
		if ($this->parameters['event']->getCountryID()) {
			$cr = new CountryRepository($app);
			$this->parameters['country'] = $cr->loadById($this->parameters['event']->getCountryID());
		}
		
		
		
		return true;

	}
	
	
	function ical($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

				
		
		$ical = new EventListICalBuilder($app, $app['currentSite'], $app['currentTimeZone'], $this->parameters['event']->getSummaryDisplay());
		$ical->addEvent($this->parameters['event']);
		return $ical->getResponse();
				
	}
	
	function json($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}


		$eventMedias = array();

		$ourRequest = new \Request($request);

		if ($ourRequest->getGetOrPostBoolean("includeMedias",false)) {
			$mrb = new MediaRepositoryBuilder($app);
			$mrb->setEvent($this->parameters['event']);
			$mrb->setIncludeDeleted(false);
			$eventMedias = $mrb->fetchAll();
		}

		$json = new EventListJSONBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$json->addEvent($this->parameters['event'], $this->parameters['groups'], 
				$this->parameters['venue'], $this->parameters['area'], $this->parameters['country'], $eventMedias);
		return $json->getResponse();
				
	}
	
	function jsonp($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		$eventMedias = array();

		$ourRequest = new \Request($request);

		if ($ourRequest->getGetOrPostBoolean("includeMedias",false)) {
			$mrb = new MediaRepositoryBuilder($app);
			$mrb->setEvent($this->parameters['event']);
			$mrb->setIncludeDeleted(false);
			$eventMedias = $mrb->fetchAll();
		}

		
		$jsonp = new EventListJSONPBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$jsonp->addEvent($this->parameters['event'], $this->parameters['groups'], 
				$this->parameters['venue'], $this->parameters['area'], $this->parameters['country'], $eventMedias);
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}
	

}


