<?php

namespace org\openacalendar\displayboard\site\controllers;

use repositories\AreaRepository;
use repositories\GroupRepository;
use repositories\VenueRepository;
use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\EventRepositoryBuilder;


/**
 *
 * @package org.openacalendar.displayboard
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class DisplayBoardController {
	
	protected $parameters;

	protected static $MAX_EVENT_QUERIES_ON_EVENT_BOARD = 5;

	function build(Application $app) {
		$this->parameters = array(
			'daysAheadInNextBox'=>3,
			'showCharsOfDescription'=>0,
			'refreshInMinutes'=>0,
			'MAX_EVENT_QUERIES_ON_EVENT_BOARD'=>  self::$MAX_EVENT_QUERIES_ON_EVENT_BOARD,
			'configParameters'=>array(),
		);
		
		if (isset($_GET['daysAheadInNextBox']) && intval($_GET['daysAheadInNextBox']) >= 0){
			$this->parameters['daysAheadInNextBox'] = intval($_GET['daysAheadInNextBox']);
			$this->parameters['configParameters']['daysAheadInNextBox'] = $_GET['daysAheadInNextBox'];
		}
		
		if (isset($_GET['showCharsOfDescription']) && intval($_GET['showCharsOfDescription']) >= 0){
			$this->parameters['showCharsOfDescription'] = intval($_GET['showCharsOfDescription']);
			$this->parameters['configParameters']['showCharsOfDescription'] = $_GET['showCharsOfDescription'];
		}

		if (isset($_GET['refreshInMinutes']) && intval($_GET['refreshInMinutes']) >= 0){
			$this->parameters['refreshInMinutes'] = intval($_GET['refreshInMinutes']);
			$this->parameters['configParameters']['refreshInMinutes'] = $_GET['refreshInMinutes'];
		}

		$areaRepository = new AreaRepository($app);
		$groupRepository = new GroupRepository($app);
		$venueRepository = new VenueRepository($app);

		$this->parameters['data'] = array();



		for ($i = 0; $i <= self::$MAX_EVENT_QUERIES_ON_EVENT_BOARD; $i++) {
			$area = null;
			if (isset($_GET['eventArea'.$i])) {
				$area = $this->getIdFromPassedVariable($_GET['eventArea'.$i]);
				$this->parameters['configParameters']['eventArea'.$i] = $_GET['eventArea'.$i];
			}
			$group = null;
			if (isset($_GET['eventGroup'.$i])) {
				$group = $this->getIdFromPassedVariable($_GET['eventGroup'.$i]);
				$this->parameters['configParameters']['eventGroup'.$i] = $_GET['eventGroup'.$i];
			}
			$venue = null;
			if (isset($_GET['eventVenue'.$i])) {
				$venue = $this->getIdFromPassedVariable($_GET['eventVenue'.$i]);
				$this->parameters['configParameters']['eventVenue'.$i] = $_GET['eventVenue'.$i];
			}
			if ($area || $group || $venue) {
				$queryData = array(
						'area'=>null,
						'group'=>null,
						'venue'=>null,
						'minorImportance'=>false,
						'query'=>new EventRepositoryBuilder($app),
					);
				$queryData['query']->setSite($app['currentSite']);
				$queryData['query']->setAfterNow();
				$queryData['query']->setIncludeDeleted(false);
				if ($area) {
					$areaObj = $areaRepository->loadBySlug($app['currentSite'],$area);
					if ($areaObj) {
						$queryData['area'] = $areaObj;
						$queryData['query']->setArea($areaObj);
					}
				}
				if ($group) {
					$groupObj = $groupRepository->loadBySlug($app['currentSite'],$group);
					if ($groupObj) {
						$queryData['group'] = $groupObj;
						$queryData['query']->setGroup($groupObj);
					}
				}
				if ($venue) {
					$venueObj = $venueRepository->loadBySlug($app['currentSite'],$venue);
					if ($venueObj) {
						$queryData['venue'] = $venueObj;
						$queryData['query']->setVenue($venueObj);
					}
				}
				if (isset($_GET['eventMinorImportance'.$i]) && $_GET['eventMinorImportance'.$i] == 'yes') {
					$queryData['minorImportance'] = true;
					$this->parameters['configParameters']['eventMinorImportance'.$i] = 'yes';
				}
				$this->parameters['data'][] = $queryData;
			}
		}

		if (count($this->parameters['data']) == 0 ) {
			$queryData = array(
					'area'=>null,
					'group'=>null,
					'venue'=>null,
					'minorImportance'=>false,
					'query'=>new EventRepositoryBuilder($app),
				);
			$queryData['query']->setSite($app['currentSite']);
			$queryData['query']->setAfterNow();
			$queryData['query']->setIncludeDeleted(false);
			$this->parameters['data'][] = $queryData;
		}

	}
	
	function index(Request $request, Application $app) {
		$this->build($app);
		
		return $app['twig']->render('displayboard/site/displayboard/index.html.twig', $this->parameters);
	}

	function run(Request $request, Application $app) {
		$this->build($app);

		// Get dates we will sort events into
		$t = \TimeSource::getDateTime();
		$t->setTimeZone(new \DateTimeZone($app['currentTimeZone']));

		$today = $t->format('d-m-Y');
		$nextDates = array();
		for ($i = 1; $i <= $this->parameters['daysAheadInNextBox']; $i++) {
			$t->add(new \DateInterval(('P1D')));
			$nextDates[] = $t->format('d-m-Y');
		}

		// Sort events into dates
		$this->parameters['eventsToday'] = array();
		$this->parameters['eventsTodayMinorImportance'] = array();
		$this->parameters['eventsNext'] = array();
		$this->parameters['eventsNextMinorImportance'] = array();
		$this->parameters['eventsLater'] = array();
		$this->parameters['eventsLaterMinorImportance'] = array();

		$eventIDsSeen = array();
		foreach($this->parameters['data'] as $queries) {
			foreach($queries['query']->fetchAll() as $event) {
				if (!in_array($event->getId(), $eventIDsSeen)) {
					$eventStart = $event->getStartAt()->format('d-m-Y');
					if ($eventStart == $today) {
						$this->parameters['eventsToday'.($queries['minorImportance']?'MinorImportance':'')][] = $event;
					} else if (in_array ($eventStart,$nextDates)) {
						$this->parameters['eventsNext'.($queries['minorImportance']?'MinorImportance':'')][] = $event;
					} else {
						$this->parameters['eventsLater'.($queries['minorImportance']?'MinorImportance':'')][] = $event;
					}
					$eventIDsSeen[] = $event->getId();
				}
			}
		}


		$cmp = function($a, $b) {
			if ($a->getStartAt()->getTimestamp() == $b->getStartAt()->getTimestamp()) {
				return 0;
			}
			return ($a->getStartAt()->getTimestamp() < $b->getStartAt()->getTimestamp()) ? -1 : 1;
		};

		usort($this->parameters['eventsToday'], $cmp);
		usort($this->parameters['eventsTodayMinorImportance'], $cmp);
		usort($this->parameters['eventsNext'], $cmp);
		usort($this->parameters['eventsNextMinorImportance'], $cmp);
		usort($this->parameters['eventsLater'], $cmp);
		usort($this->parameters['eventsLaterMinorImportance'], $cmp);
		
		
		return $app['twig']->render('displayboard/site/displayboard/run.html.twig', $this->parameters);
	}

	protected function getIdFromPassedVariable($var) {
		if (strpos($var, "-")) {
			$var = array_shift(explode("-", $var, 2));
		}
		return intval($var);

	}

}

