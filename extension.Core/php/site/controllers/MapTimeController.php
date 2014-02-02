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
class MapTimeController {
	
	function index(Application $app) {
		

		$start = \TimeSource::getDateTime();
		
		
		
		return $app['twig']->render('site/maptime/index.html.twig', array(	
				'start'=>$start,
			));
		
	}
	
	function getDataJson(Application $app) {
		
		$steps = 120;
		$venueRepo = new VenueRepository();
		$data = array('data'=>array());
			
		if ($_POST['speed'] == '3600') {
			$interval = new \DateInterval("PT1H");
		} else if ($_POST['speed'] == '21600') { 
			$interval = new \DateInterval("PT6H");
		} else { 
			$interval = new \DateInterval("PT1M");
		}
	
		$start = new \DateTime("", new \DateTimeZone($app['currentTimeZone']));
		$start->setDate($_POST['year'], $_POST['month'], $_POST['day']);
		$start->setTime($_POST['hour'], $_POST['min'], 0);
		$time = clone $start;
		$end = clone $start;
		for ($i = 1; $i <= $steps; $i++) {
			$end->add($interval);
		}

		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
		$erb->setAfter($start);
		$erb->setBefore($end);
		$erb->setIncludeDeleted(false);
		$erb->setVenueLatLngOnly(true);
		$events = $erb->fetchAll();

		$eventsStatus = array();
		
		for ($i = 1; $i <= $steps; $i++) {
			$thisData = array(
				'year'=>$time->format('Y'),
				'month'=>$time->format('n'),
				'day'=>$time->format('j'),
				'hour'=>$time->format('H'),
				'min'=>$time->format('i'),
				'events'=>array(),
				'eventsContinuing'=>array(),
			);
			
			foreach($events as $event) {
				if (!isset($eventsStatus[$event->getId()])) {
					if ($event->getStartAt()->getTimestamp() < $time->getTimestamp()) {
						$eventsStatus[$event->getId()] = true;
						$thisData['events'][$event->getSlug()] = array(
								'slug'=>$event->getSlug(),
								'venue_slug'=>$event->getVenueSlug(),
								'venue_lat'=>$event->getVenueLat(),
								'venue_lng'=>$event->getVenueLng(),
								'venue_title'=>$event->getVenueTitle(),
								'event_title'=>$event->getSummaryDisplay(),
							); 
					}
				} else {
					if ($event->getEndAt()->getTimestamp() >= $time->getTimestamp()) {
						$thisData['eventsContinuing'][$event->getSlug()] = true;
					}
				}
			}
			
			
			$data['data'][] = $thisData;			
			$time->add($interval);
		}
		
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;	
		
	}
	
}

