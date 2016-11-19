<?php

namespace org\openacalendar\displayboard\site\controllers;

use repositories\AreaRepository;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use repositories\CountryRepository;
use repositories\GroupRepository;
use repositories\ImportRepository;
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

class DisplayBoardCycleDetailsController {
	
	protected $parameters;

	protected static $MAX_EVENT_QUERIES_ON_EVENT_BOARD = 5;

	function build(Application $app) {
		$this->parameters = array(
			'refreshInSeconds'=>30,
			'configParameters'=>array(),
			'MAX_EVENT_QUERIES_ON_EVENT_BOARD'=>  self::$MAX_EVENT_QUERIES_ON_EVENT_BOARD,
			'currentEventOffset'=>0,
			'daysInAdvance'=>40,
		);


		if (isset($_GET['currentEventOffset']) && intval($_GET['currentEventOffset']) >= 0){
			$this->parameters['currentEventOffset'] = intval($_GET['currentEventOffset']);
		}

		if (isset($_GET['refreshInSeconds']) && intval($_GET['refreshInSeconds']) >= 0){
			$this->parameters['refreshInSeconds'] = intval($_GET['refreshInSeconds']);
		}

		if (isset($_GET['daysInAdvance']) && intval($_GET['daysInAdvance']) >= 0){
			$this->parameters['daysInAdvance'] = intval($_GET['daysInAdvance']);
		}


		$areaRepository = new AreaRepository($app);
		$groupRepository = new GroupRepository($app);
		$venueRepository = new VenueRepository($app);

		$this->parameters['data'] = array();


		$before = new \DateTime();
		$before->add(new \DateInterval("P".$this->parameters['daysInAdvance']."D"));

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
						'query'=>new EventRepositoryBuilder($app),
					);
				$queryData['query']->setSite($app['currentSite']);
				$queryData['query']->setAfterNow();
				$queryData['query']->setBefore($before);
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
				$this->parameters['data'][] = $queryData;
			}
		}

		if (count($this->parameters['data']) == 0 ) {
			$queryData = array(
					'area'=>null,
					'group'=>null,
					'venue'=>null,
					'query'=>new EventRepositoryBuilder($app),
				);
			$queryData['query']->setSite($app['currentSite']);
			$queryData['query']->setAfterNow();
			$queryData['query']->setBefore($before);
			$queryData['query']->setIncludeDeleted(false);
			$this->parameters['data'][] = $queryData;
		}

	}
	
	function index(Request $request, Application $app) {
		$this->build($app);
		
		return $app['twig']->render('displayboard/site/displayboard/cycledetails/index.html.twig', $this->parameters);
	}

	/**
	 * @param Request $request
	 * @param Application $app
	 *
	 * @return mixed
	 */
	function run(Request $request, Application $app) {
		$this->build($app);

		// ############### Get all events, find the one we want!
		$events = array();

		$eventIDsSeen = array();
		foreach($this->parameters['data'] as $queries) {
			foreach($queries['query']->fetchAll() as $event) {
				if (!in_array($event->getId(), $eventIDsSeen)) {
					$events[] = $event;
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

		usort($events, $cmp);

		if ($this->parameters['currentEventOffset'] > 0) {
			if ($this->parameters['currentEventOffset'] >= count($events)) {
				$this->parameters['currentEventOffset'] = 0;
			} else {
				for ( $i = 0; $i < $this->parameters['currentEventOffset']; $i ++ ) {
					array_shift( $events );
				}
			}
		}

		$this->parameters['event'] = array_shift($events);

		if (!$this->parameters['event']) {
			return $app['twig']->render('displayboard/site/displayboard/cycledetails/run.noevent.html.twig', $this->parameters);
		}

		// ############### Get Extra Info about event!
		$this->parameters['parentAreas'] = array();
		$this->parameters['country'] = null;
		$this->parameters['venue'] = null;
		$this->parameters['area'] = null;
		$this->parameters['groups'] = array();
		$this->parameters['group'] = null;
		$this->parameters['medias'] = array();
		$this->parameters['mediasForGroup'] = array();
		$this->parameters['mediasForVenue'] = array();
		$this->parameters['mediasForEvent'] = array();
		$this->parameters['tags'] = array();

		if ($this->parameters['event']->getCountryID()) {
			$cr = new CountryRepository($app);
			$this->parameters['country'] = $cr->loadById($this->parameters['event']->getCountryID());
		}

		$areaID = null;
		if ($this->parameters['event']->getVenueID()) {
			$cr = new VenueRepository($app);
			$this->parameters['venue'] = $cr->loadById($this->parameters['event']->getVenueID());
			$areaID = $this->parameters['venue']->getAreaId();
		} else if ($this->parameters['event']->getAreaId()) {
			$areaID = $this->parameters['event']->getAreaId();
		}

		if ($areaID) {
			$ar = new AreaRepository($app);
			$this->parameters['area'] = $ar->loadById($areaID);
			if (!$this->parameters['area']) {
				return false;
			}

			$checkArea = $this->parameters['area']->getParentAreaId() ? $ar->loadById($this->parameters['area']->getParentAreaId())  : null;
			while($checkArea) {
				array_unshift($this->parameters['parentAreas'],$checkArea);
				$checkArea = $checkArea->getParentAreaId() ? $ar->loadById($checkArea->getParentAreaId())  : null;
			}
		}

		if ($this->parameters['event']->getImportId()) {
			$iur = new ImportRepository($app);
			$this->parameters['import'] = $iur->loadById($this->parameters['event']->getImportId());
		}

		$groupRB = new GroupRepositoryBuilder($app);
		$groupRB->setEvent($this->parameters['event']);
		$this->parameters['groups'] = $groupRB->fetchAll();
		if ($this->parameters['event']->getGroupId()) {
			foreach($this->parameters['groups'] as $group)  {
				if ($group->getId() == $this->parameters['event']->getGroupId()) {
					$this->parameters['group'] = $group;
				}
			}
		}


		if ($app['config']->isFileStore()) {
			foreach($this->parameters['groups'] as $group) {
				$mrb = new MediaRepositoryBuilder($app);
				$mrb->setIncludeDeleted(false);
				$mrb->setSite($app['currentSite']);
				$mrb->setGroup($group);
				$this->parameters['mediasForGroup'][$group->getSlug()] = $mrb->fetchAll();
				$this->parameters['medias'] = array_merge($this->parameters['medias'],$this->parameters['mediasForGroup'][$group->getSlug()]);
			}

			if ($this->parameters['venue']) {
				$mrb = new MediaRepositoryBuilder($app);
				$mrb->setIncludeDeleted(false);
				$mrb->setSite($app['currentSite']);
				$mrb->setVenue($this->parameters['venue']);
				$this->parameters['mediasForVenue'] = $mrb->fetchAll();
				$this->parameters['medias'] = array_merge($this->parameters['medias'],$this->parameters['mediasForVenue']);
			}

			$mrb = new MediaRepositoryBuilder($app);
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setEvent($this->parameters['event']);
			$this->parameters['mediasForEvent'] = $mrb->fetchAll();
			$this->parameters['medias'] = array_merge($this->parameters['medias'],$this->parameters['mediasForEvent']);
		}

		if ($app['currentSiteFeatures']->has('org.openacalendar','Tag')) {
			$trb = new TagRepositoryBuilder($app);
			$trb->setSite($app['currentSite']);
			$trb->setIncludeDeleted(false);
			$trb->setTagsForEvent($this->parameters['event']);
			$this->parameters['tags'] = $trb->fetchAll();
		}

		// ############### Add our info, show page!
		$this->getNextPageURL();


		return $app['twig']->render('displayboard/site/displayboard/cycledetails/run.html.twig', $this->parameters);
	}

    protected function getIdFromPassedVariable($var) {
        if (strpos($var, "-")) {
            $var = substr($var, 0, strpos($var, "-"));
        }
        return intval($var);
    }

	protected function getNextPageURL() {
		$url = '/displayboard/cycledetails/run?';

		$url .= "refreshInSeconds=". urlencode($this->parameters['refreshInSeconds'])."&";
		$url .= "daysInAdvance=". urlencode($this->parameters['daysInAdvance'])."&";
		$url .= "currentEventOffset=". urlencode($this->parameters['currentEventOffset'] + 1)."&";

		foreach($this->parameters['configParameters'] as $k=>$v) {
			$url .= $k."=".urlencode($v)."&";
		}

		$this->parameters['nextPageURL'] = $url;

	}
}

