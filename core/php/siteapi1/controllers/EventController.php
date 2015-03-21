<?php

namespace siteapi1\controllers;

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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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

		$eventRepository = new EventRepository();
		$this->parameters['event'] =  $eventRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['event']) {
			return false;
		}

		if ($this->parameters['event']->getGroupId()) {
			$grb = new GroupRepositoryBuilder();
			$grb->setEvent($this->parameters['event']);
			$this->parameters['groups'] = $grb->fetchAll();
		}
		
		if ($this->parameters['event']->getVenueID()) {
			$vr = new VenueRepository();
			$this->parameters['venue'] = $vr->loadById($this->parameters['event']->getVenueID());
		}
		
		if ($this->parameters['event']->getAreaID()) {
			$ar = new AreaRepository();
			$this->parameters['area'] = $ar->loadById($this->parameters['event']->getAreaID());
		} elseif ($this->parameters['venue'] && $this->parameters['venue']->getAreaId()) {
			$ar = new AreaRepository();
			$this->parameters['area'] = $ar->loadById($this->parameters['venue']->getAreaID());
		}
		
		
		if ($this->parameters['event']->getCountryID()) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadById($this->parameters['event']->getCountryID());
		}
		
		
		
		return true;

	}
	
	
	function ical($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

				
		
		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'], $this->parameters['event']->getSummaryDisplay());
		$ical->addEvent($this->parameters['event']);
		return $ical->getResponse();
				
	}
	
	function json($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

				
		
		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->addEvent($this->parameters['event'], $this->parameters['groups'], 
				$this->parameters['venue'], $this->parameters['area'], $this->parameters['country']);
		return $json->getResponse();
				
	}
	
	function jsonp($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app['currentSite'], $app['currentTimeZone']);
		$jsonp->addEvent($this->parameters['event'], $this->parameters['groups'], 
				$this->parameters['venue'], $this->parameters['area'], $this->parameters['country']);
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}
	

}


