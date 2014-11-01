<?php

namespace org\openacalendar\displayboard\site\controllers;

use repositories\AreaRepository;
use repositories\GroupRepository;
use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\EventRepositoryBuilder;


/**
 *
 * @package org.openacalendar.displayboard
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class DisplayBoardController {
	
	protected $paramaters;

	protected static $MAX_EVENT_QUERIES_ON_EVENT_BOARD = 5;

	function build(Application $app) {
		$this->paramaters = array(
			'daysAheadInNextBox'=>3,
			'showCharsOfDescription'=>0,
			'refreshInMinutes'=>0,
			'MAX_EVENT_QUERIES_ON_EVENT_BOARD'=>  self::$MAX_EVENT_QUERIES_ON_EVENT_BOARD,
		);
		
		if (isset($_GET['daysAheadInNextBox']) && intval($_GET['daysAheadInNextBox']) >= 0){
			$this->paramaters['daysAheadInNextBox'] = intval($_GET['daysAheadInNextBox']);
		}
		
		if (isset($_GET['showCharsOfDescription']) && intval($_GET['showCharsOfDescription']) >= 0){
			$this->paramaters['showCharsOfDescription'] = intval($_GET['showCharsOfDescription']);
		}

		if (isset($_GET['refreshInMinutes']) && intval($_GET['refreshInMinutes']) >= 0){
			$this->paramaters['refreshInMinutes'] = intval($_GET['refreshInMinutes']);
		}

		$areaRepository = new AreaRepository();
		$groupRepository = new GroupRepository();

		$this->paramaters['data'] = array();

		for ($i = 0; $i <= self::$MAX_EVENT_QUERIES_ON_EVENT_BOARD; $i++) {
			$area = isset($_GET['eventArea'.$i]) ? intval($_GET['eventArea'.$i]) : null;
			$group = isset($_GET['eventGroup'.$i]) ? intval($_GET['eventGroup'.$i]) : null;
			if ($area || $group) {
				$queryData = array(
						'area'=>null,
						'group'=>null,
						'minorImportance'=>false,
						'query'=>new EventRepositoryBuilder(),
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
				if (isset($_GET['eventMinorImportance'.$i]) && $_GET['eventMinorImportance'.$i] == 'yes') {
					$queryData['minorImportance'] = true;
				}
				$this->paramaters['data'][] = $queryData;
			}
		}

		if (count($this->paramaters['data']) == 0 ) {
			$queryData = array(
					'area'=>null,
					'group'=>null,
					'minorImportance'=>false,
					'query'=>new EventRepositoryBuilder(),
				);
			$queryData['query']->setSite($app['currentSite']);
			$queryData['query']->setAfterNow();
			$queryData['query']->setIncludeDeleted(false);
			$this->paramaters['data'][] = $queryData;
		}

	}
	
	function index(Request $request, Application $app) {
		$this->build($app);
		
		return $app['twig']->render('displayboard/site/displayboard/index.html.twig', $this->paramaters);
	}

	function run(Request $request, Application $app) {
		$this->build($app);

		// Get dates we will sort events into
		$t = \TimeSource::getDateTime();
		$t->setTimeZone(new \DateTimeZone($app['currentTimeZone']));

		$today = $t->format('d-m-Y');
		$nextDates = array();
		for ($i = 1; $i <= $this->paramaters['daysAheadInNextBox']; $i++) {
			$t->add(new \DateInterval(('P1D')));
			$nextDates[] = $t->format('d-m-Y');
		}

		// Sort events into dates
		$this->paramaters['eventsToday'] = array();
		$this->paramaters['eventsTodayMinorImportance'] = array();
		$this->paramaters['eventsNext'] = array();
		$this->paramaters['eventsNextMinorImportance'] = array();
		$this->paramaters['eventsLater'] = array();
		$this->paramaters['eventsLaterMinorImportance'] = array();

		$eventIDsSeen = array();
		foreach($this->paramaters['data'] as $queries) {
			foreach($queries['query']->fetchAll() as $event) {
				if (!in_array($event->getId(), $eventIDsSeen)) {
					$eventStart = $event->getStartAt()->format('d-m-Y');
					if ($eventStart == $today) {
						$this->paramaters['eventsToday'.($queries['minorImportance']?'MinorImportance':'')][] = $event;
					} else if (in_array ($eventStart,$nextDates)) {
						$this->paramaters['eventsNext'.($queries['minorImportance']?'MinorImportance':'')][] = $event;
					} else {
						$this->paramaters['eventsLater'.($queries['minorImportance']?'MinorImportance':'')][] = $event;
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

		usort($this->paramaters['eventsToday'], $cmp);
		usort($this->paramaters['eventsTodayMinorImportance'], $cmp);
		usort($this->paramaters['eventsNext'], $cmp);
		usort($this->paramaters['eventsNextMinorImportance'], $cmp);
		usort($this->paramaters['eventsLater'], $cmp);
		usort($this->paramaters['eventsLaterMinorImportance'], $cmp);
		
		
		return $app['twig']->render('displayboard/site/displayboard/run.html.twig', $this->paramaters);
	}

}

