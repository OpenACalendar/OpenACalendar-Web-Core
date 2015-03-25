<?php

namespace site\controllers;

use models\EventEditMetaDataModel;
use repositories\AreaRepository;
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
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventNewController {
	
	
	function newEvent(Request $request, Application $app) {

		if (!$app['currentUser'] && !$app['currentUserActions']->has("org.openacalendar","eventNew") &&  $app['anyVerifiedUserActions']->has("org.openacalendar","eventNew")) {
			return $app['twig']->render('site/eventnew/new.useraccountneeded.html.twig', array());
		}

		if (!$app['currentUser']) {
			$app->abort(403, "Not allowed");
		}

		$params = array('when'=>null, 'date'=>null, 'area'=>null);

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

		if ($request->query->has("area")) {
			$areaRepo = new AreaRepository();
			$params['area'] = $areaRepo->loadBySlug($app['currentSite'], $request->query->get("area"));
		}

		if ($app['currentSite']->getIsFeatureGroup()) {
			return $app['twig']->render('site/eventnew/new.groups.html.twig', $params);
		} else {
			return $app['twig']->render('site/eventnew/new.nogroups.html.twig', $params);
		}

	}
	
	function newEventGo(Request $request, Application $app) {		
		$parseResult = null;

		$params = array('area'=>null);

		$event = new EventModel();
		$event->setStartAt(\TimeSource::getDateTime());
		$event->setEndAt(\TimeSource::getDateTime());

		// what
		if (isset($_GET['what']) && trim($_GET['what'])) {
			$event->setSummary($_GET['what']);
		}
		// when
		if (isset($_GET['when']) && trim($_GET['when'])) {
			$parse = new ParseDateTimeRangeString(\TimeSource::getDateTime(), $app['currentTimeZone']);
			$parseResult = $parse->parse($_GET['when']);
			if ($parseResult->getStart()) {
				$event->setStartAt($parseResult->getStart());
				// If no end is returned, just set start as sensible default
				$event->setEndAt($parseResult->getEnd() ? $parseResult->getEnd() : $parseResult->getStart());
			}
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
		// where
		if ($request->query->has("area")) {
			$areaRepo = new AreaRepository();
			$params['area'] = $areaRepo->loadBySlug($app['currentSite'], $request->query->get("area"));
			$event->setAreaId($params['area']->getId());
		}

		$event->setDefaultOptionsFromSite($app['currentSite']);

		$timezone = isset($_POST['EventNewForm']) && isset($_POST['EventNewForm']['timezone']) ? $_POST['EventNewForm']['timezone'] : $app['currentTimeZone'];
		$form = $app['form.factory']->create(new EventNewForm($timezone, $app), $event);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				$eventEditMetaData = new EventEditMetaDataModel();
				$eventEditMetaData->setUserAccount($app['currentUser']);
				if ($form->has('edit_comment')) {
					$eventEditMetaData->setEditComment($form->get('edit_comment')->getData());
				}

				$eventRepository = new EventRepository();
				$eventRepository->createWithMetaData($event, $app['currentSite'], $eventEditMetaData);
				
				if ($parseResult && $app['config']->logFileParseDateTimeRange) {

					$parseStart = clone $parseResult->getStart();
					$parseStart->setTimezone(new \DateTimeZone('UTC'));
					$parseEnd = clone $parseResult->getEnd();
					$parseEnd->setTimezone(new \DateTimeZone('UTC'));

					$success = $parseStart->getTimestamp() == $event->getStartAt()->getTimestamp()
						&& $parseEnd->getTimestamp() == $event->getEndAt()->getTimestamp();

					$handle = fopen($app['config']->logFileParseDateTimeRange, "a");
					$now = \TimeSource::getDateTime();
					fputcsv($handle, array(
						'Site',
						$app['currentSite']->getId(),
						$app['currentSite']->getSlug(),
						'Event',
						$event->getSlug(),
						'Now',
						$now->format("c"),
						'Wanted Timezone',
						$event->getTimezone(),
						'Wanted Start UTC',
						$event->getStartAtInUTC()->format("c"),
						'Wanted End UTC',
						$event->getEndAtInUTC()->format("c"),
						'Typed',
						$_GET['when'],
						'Got Start UTC',
						$parseStart->format("c"),
						'Got End UTC',
						$parseEnd->format("c"),
						($success ? 'SUCCESS' : 'FAIL'),
					));
					fclose($handle);
				}


				if ($event->getIsPhysical() && $app['currentSite']->getIsFeaturePhysicalEvents()) {
					return $app->redirect("/event/".$event->getSlugForURL().'/edit/venue');
				} else {
					return $app->redirect("/event/".$event->getSlugForURL());
				}
				
			}
		}

		$params['form'] = $form->createView();
		
		return $app['twig']->render('site/eventnew/newGo.html.twig', $params);
		
	}
	
	
	public function creatingThisNewEvent(Request $request, Application $app) {		
		$notDuplicateSlugs = isset($_GET['notDuplicateSlugs']) ? explode(",", $_GET['notDuplicateSlugs']) : array();

		$event = new EventModel();
		$event->setDefaultOptionsFromSite($app['currentSite']);
		$timezone = isset($_POST['EventNewForm']) && isset($_POST['EventNewForm']['timezone']) ? $_POST['EventNewForm']['timezone'] : $app['currentTimeZone'];
		$form = $app['form.factory']->create(new EventNewForm($timezone, $app), $event);
		$form->bind($request);

		$data = array('duplicates'=>array());

		if ($event->getStartAtInUTC()->getTimestamp() > $event->getEndAtInUTC()->getTimestamp()) {
			$data['readableStartEndRange'] = "(The start is after the end)";
		} else {
			$data['readableStartEndRange'] = $app['twig']->render('site/eventnew/startendrange.html.twig', array('event' => $event));
		}

		if ($app['config']->findDuplicateEventsShow > 0) {


			if ($request->request->get('group_slug')) {
				$gr = new GroupRepository();
				$group = $gr->loadBySlug($app['currentSite'], $request->request->get('group_slug'));
				if ($group) {
					$event->setGroup($group);
				}
			}

			if ($request->request->has("area")) {
				$areaRepo = new AreaRepository();
				$params['area'] = $areaRepo->loadBySlug($app['currentSite'], $request->request->get("area"));
				$event->setAreaId($params['area']->getId());
			}

			$searchForDuplicateEvents = new SearchForDuplicateEvents(
				$event,
				$app['currentSite'],
				$app['config']->findDuplicateEventsShow,
				$app['config']->findDuplicateEventsThreshhold,
				is_array($app['config']->findDuplicateEventsNoMatchSummary) ? $app['config']->findDuplicateEventsNoMatchSummary : array()
			);
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


