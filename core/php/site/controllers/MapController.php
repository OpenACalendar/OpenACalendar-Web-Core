<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\EventRepositoryBuilder;
use repositories\VenueRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MapController {

	
	protected $parameters = array();
	
	protected function build($countryCode, $areaSlug, $venueSlug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'area'=>null,'venue'=>null);
		
		if ($areaSlug) {
			$ar = new AreaRepository();
			$this->parameters['area'] = $ar->loadBySlug($app['currentSite'], $areaSlug);
		}
		
		if ($this->parameters['area']) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadById($this->parameters['area']->getCountryID());
		} else if ($countryCode) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadByTwoCharCode($countryCode);
		}

		if ($venueSlug) {
			$vr = new VenueRepository();
			$this->parameters['venue'] = $vr->loadBySlug($app['currentSite'], $venueSlug);
		}

		return true;
	}
	
	
	function index(Application $app, Request $request) {
		
		$this->build(
				isset($_GET['country']) ? $_GET['country'] : null,
				isset($_GET['area']) ? $_GET['area'] : null,
				isset($_GET['venue']) ? $_GET['venue'] : null,
				$request, $app);
		
		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
		$erb->setAfterNow();
		$erb->setIncludeDeleted(false);
		$erb->setMustHaveLatLng(true);
		
		$events = $erb->fetchAll();
		
		$this->parameters['venueData'] = array();
		
		if ($this->parameters['venue']) {
			$this->parameters['venueData'][$this->parameters['venue']->getId()] = array(
				'venue_lat'=> $this->parameters['venue']->getLat(),
				'venue_lng'=> $this->parameters['venue']->getLng(),
				'venue_title'=> $this->parameters['venue']->getTitle(),
				'venue_slug'=> $this->parameters['venue']->getSlug(),
			);
		}
		
		foreach($events as $event) {
			$this->parameters['venueData'][$event->getVenueId()] = array(
				'venue_lat'=> $event->getVenue()->getLat(),
				'venue_lng'=> $event->getVenue()->getLng(),
				'venue_title'=> $event->getVenue()->getTitle(),
				'venue_slug'=> $event->getVenue()->getSlug(),
			);
		}
		
		return $app['twig']->render('site/mapPage.html.twig', $this->parameters);
		
	}
	
}

