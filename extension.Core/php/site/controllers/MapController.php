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
	
	protected function build($countryCode, $areaSlug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'area'=>array());
		
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

		return true;
	}
	
	
	function index(Application $app, Request $request) {
		
		$this->build(
				isset($_GET['country'])?$_GET['country']:null, 
				isset($_GET['area'])?$_GET['area']:null, 
				$request, $app);
		
		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
		$erb->setAfterNow();
		$erb->setIncludeDeleted(false);
		$erb->setVenueLatLngOnly(true);
		
		$events = $erb->fetchAll();
		
		$this->parameters['venueData'] = array();
		
		$venueRepo = new VenueRepository();
		
		foreach($events as $event) {
			$this->parameters['venueData'][$event->getVenueId()] = array(
				'venue_lat'=> $event->getVenueLat(),
				'venue_lng'=> $event->getVenueLng(),
				'venue_title'=> $event->getVenueTitle(),
				'venue_slug'=> $event->getVenueSlug(),
			);
		}
		
		return $app['twig']->render('site/mapPage.html.twig', $this->parameters);
		
	}
	
}

