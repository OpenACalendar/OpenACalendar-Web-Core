<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\EventRepositoryBuilder;
use repositories\VenueRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MapController {
	
	function index(Application $app) {
		
		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
		$erb->setAfterNow();
		$erb->setIncludeDeleted(false);
		$erb->setVenueLatLngOnly(true);
		
		$events = $erb->fetchAll();
		
		$venues = array();
		
		$venueRepo = new VenueRepository();
		
		foreach($events as $event) {
			$venues[$event->getVenueId()] = array(
				'venue_lat'=> $event->getVenueLat(),
				'venue_lng'=> $event->getVenueLng(),
				'venue_title'=> $event->getVenueTitle(),
				'venue_slug'=> $event->getVenueSlug(),
			);
		}
		
		return $app['twig']->render('site/mapPage.html.twig', array(
				'venueData'=>$venues,
			));
		
	}
	
}

