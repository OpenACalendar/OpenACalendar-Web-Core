<?php

namespace site\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\EventRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class DisplayBoardController {
	
	protected $paramaters;

	function build() {
		$this->paramaters = array(
			'daysAheadInNextBox'=>3,
			'showCharsOfDescription'=>0,
		);
		
		if (isset($_GET['daysAheadInNextBox']) && intval($_GET['daysAheadInNextBox']) > 0){
			$this->paramaters['daysAheadInNextBox'] = intval($_GET['daysAheadInNextBox']);
		}
		
		if (isset($_GET['showCharsOfDescription']) && intval($_GET['showCharsOfDescription']) > 0){
			$this->paramaters['showCharsOfDescription'] = intval($_GET['showCharsOfDescription']);
		}
	}
	
	function index(Request $request, Application $app) {
		$this->build();
		
		return $app['twig']->render('site/displayboard/index.html.twig', $this->paramaters);
	}

	function run(Request $request, Application $app) {
		$this->build();

		// Get dates we will sort events into
		$t = \TimeSource::getDateTime();
		$t->setTimeZone(new \DateTimeZone($app['currentTimeZone']));
		
		$today = $t->format('d-m-Y');
		$nextDates = array();
		for ($i = 1; $i <= $this->paramaters['daysAheadInNextBox']; $i++) {
			$t->add(new \DateInterval(('P1D')));
			$nextDates[] = $t->format('d-m-Y');
		}
		
		// Fetch events
		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
		$erb->setAfterNow();
		$erb->setIncludeDeleted(false);
		$events = $erb->fetchAll();
		
		// Sort events into dates
		$this->paramaters['eventsToday'] = array();
		$this->paramaters['eventsNext'] = array();
		$this->paramaters['eventsLater'] = array();
		
		foreach($events as $event) {
			$eventStart = $event->getStartAt()->format('d-m-Y');
			if ($eventStart == $today) {
				$this->paramaters['eventsToday'][] = $event;
			} else if (in_array ($eventStart,$nextDates)) {
				$this->paramaters['eventsNext'][] = $event;
			} else {
				$this->paramaters['eventsLater'][] = $event;
			}
		}
		
		
		return $app['twig']->render('site/displayboard/run.html.twig', $this->paramaters);
	}

}

