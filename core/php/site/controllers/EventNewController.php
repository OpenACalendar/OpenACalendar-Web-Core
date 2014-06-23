<?php

namespace site\controllers;

use Silex\Application;
use site\forms\EventNewForm;
use site\forms\EventEditForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\SiteModel;
use models\EventModel;
use repositories\EventRepository;
use repositories\GroupRepository;
use JMBTechnologyLimited\ParseDateTimeRangeString\ParseDateTimeRangeString;
use \SearchForDuplicateEvents;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventNewController {
	
	
	function newEvent(Request $request, Application $app) {
		
		$params = array('when'=>null, 'date'=>null);
		
		if (isset($_GET['date']) && trim($_GET['date'])) {
			$bits = explode("-", $_GET['date']);
			if (count($bits) == 3 && intval($bits[0]) && intval($bits[1]) && intval($bits[2])) {
				$whenObj = \TimeSource::getDateTime();
				$whenObj->setTimezone(new \DateTimeZone($app['currentTimeZone']));
				$whenObj->setDate($bits[0], $bits[1], $bits[2]);
				$params['when'] = $whenObj->format("jS F Y");
				$params['date'] = $_GET['date'];
			}
		}
		
		
		
		if ($app['currentSite']->getIsFeatureGroup()) {
			return $app['twig']->render('site/eventnew/new.groups.html.twig', $params);
		} else {
			return $app['twig']->render('site/eventnew/new.nogroups.html.twig', $params);
		}
		
	}
	
	function newEventGo(Request $request, Application $app) {
		global $CONFIG;
		
		$parseResult = null;
		

		$event = new EventModel();
		if (isset($_GET['what']) && trim($_GET['what'])) {
			$event->setSummary($_GET['what']);
		}
		if (isset($_GET['when']) && trim($_GET['when'])) {
			$parse = new ParseDateTimeRangeString(\TimeSource::getDateTime(), $app['currentTimeZone']);
			$parseResult = $parse->parse($_GET['when']);
			$event->setStartAt($parseResult->getStart());
			$event->setEndAt($parseResult->getEnd());
		} else if (isset($_GET['date']) && trim($_GET['date'])) {
			$bits = explode("-", $_GET['date']);
			if (count($bits) == 3 && intval($bits[0]) && intval($bits[1]) && intval($bits[2])) {
				$start = \TimeSource::getDateTime();
				$start->setTimezone(new \DateTimeZone($app['currentTimeZone']));
				$start->setDate($bits[0], $bits[1], $bits[2]);
				$start->setTime(9, 0, 0);
				$event->setStartAt($start);
				$end = clone $start;
				$end->setTime(17, 0, 0);
				$event->setEndAt($end);
			}
		}
		$event->setDefaultOptionsFromSite($app['currentSite']);

		$timezone = isset($_POST['EventNewForm']) && isset($_POST['EventNewForm']['timezone']) ? $_POST['EventNewForm']['timezone'] : $app['currentTimeZone'];
		$form = $app['form.factory']->create(new EventNewForm($app['currentSite'], $timezone), $event);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->create($event, $app['currentSite'], userGetCurrent());
				
				if ($parseResult && $CONFIG->logFileParseDateTimeRange && 
						($parseResult->getStart()->getTimestamp() != $event->getStartAt()->getTimestamp() 
						|| $parseResult->getEnd()->getTimestamp() != $event->getEndAt()->getTimestamp())) {
					
					$handle = fopen($CONFIG->logFileParseDateTimeRange, "a");
					$now = \TimeSource::getDateTime();
					fwrite($handle, 'Site, '.$app['currentSite']->getId()." ,". $app['currentSite']->getSlug()." ,".
							'Event,'.$event->getSlug()." ,".
							'Now,'.$now->format("c") . "," . 
							'Wanted Start,'.$event->getStartAt()->format("c") . " ," . 
							'Wanted End,'.$event->getEndAt()->format("c") . " ," . 
							'Typed,'.str_replace("\n", " ", $_GET['when']) . "\n");
					fclose($handle);
					
				}
				
				
				if ($event->getIsPhysical() && $app['currentSite']->getIsFeaturePhysicalEvents()) {
					return $app->redirect("/event/".$event->getSlugForURL().'/edit/venue');
				} else {
					return $app->redirect("/event/".$event->getSlugForURL());
				}
				
			}
		}
		
		
		return $app['twig']->render('site/eventnew/newGo.html.twig', array(
				'form'=>$form->createView(),
			));
		
	}
	
	
	public function creatingThisNewEvent(Request $request, Application $app) {
		global $CONFIG;
		
		$notDuplicateSlugs = isset($_GET['notDuplicateSlugs']) ? explode(",", $_GET['notDuplicateSlugs']) : array();
		
		$data = array('duplicates'=>array());

		if ($CONFIG->findDuplicateEventsShow > 0) {

			$event = new EventModel();
			$event->setDefaultOptionsFromSite($app['currentSite']);
			$form = $app['form.factory']->create(new EventNewForm($app['currentSite'], $app['currentTimeZone']), $event);
			$form->bind($request);

			if ($request->request->get('group_slug')) {
				$gr = new GroupRepository();
				$group = $gr->loadBySlug($app['currentSite'], $request->request->get('group_slug'));
				if ($group) {
					$event->setGroup($group);
				}
			}

			$searchForDuplicateEvents = new SearchForDuplicateEvents($event, $app['currentSite'], 
					$CONFIG->findDuplicateEventsShow, $CONFIG->findDuplicateEventsThreshhold);
			$searchForDuplicateEvents->setNotDuplicateSlugs($notDuplicateSlugs);

			$timeZone = new \DateTimeZone($event->getTimezone());
			foreach($searchForDuplicateEvents->getPossibleDuplicates() as $dupeEvent) {
				$start = clone $dupeEvent->getStartAt();
				$start->setTimezone($timeZone);
				$data['duplicates'][] = array(
					'slug'=>$dupeEvent->getSlug(),
					'summary'=>$dupeEvent->getSummary(),
					'description'=>$dupeEvent->getDescription(),
					'startDay'=>$start->format("D"),
					'startDate'=>$start->format("jS"),
					'startMonthYear'=>$start->format("M \'y"),
					'startTime'=>$start->format("g:ia"),
				);
			}
		
		}
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;		
		
	}
}


