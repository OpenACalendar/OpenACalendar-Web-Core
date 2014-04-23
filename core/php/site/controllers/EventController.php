<?php

namespace site\controllers;

use Silex\Application;
use site\forms\EventNewForm;
use site\forms\EventEditForm;
use site\forms\EventImportedEditForm;
use site\forms\EventDeleteForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\SiteModel;
use models\EventModel;
use models\EventRecurSetModel;
use models\GroupModel;
use models\VenueModel;
use models\AreaModel;
use repositories\EventRepository;
use repositories\EventHistoryRepository;
use repositories\builders\AreaRepositoryBuilder;
use repositories\GroupRepository;
use repositories\CountryRepository;
use repositories\VenueRepository;
use repositories\EventRecurSetRepository;
use repositories\UserAtEventRepository;
use repositories\ImportURLRepository;
use repositories\AreaRepository;
use repositories\UserWatchesGroupRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\EventHistoryRepositoryBuilder;
use repositories\builders\CuratedListRepositoryBuilder;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\UserAtEventRepositoryBuilder;
use repositories\builders\GroupRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController {
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array(
			'group'=>null,
			'venue'=>null,
			'country'=>null,
			'area'=>null, 
			'parentAreas'=>array(), 
			'childAreas'=>array(),
			'importurl'=>null, 			
		);

		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$eventRepository = new EventRepository();
		$this->parameters['event'] =  $eventRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['event']) {
			return false;
		}
		
		if ($this->parameters['event']->getCountryID()) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadById($this->parameters['event']->getCountryID());
		}
		
		$areaID = null;
		if ($this->parameters['event']->getVenueID()) {
			$cr = new VenueRepository();
			$this->parameters['venue'] = $cr->loadById($this->parameters['event']->getVenueID());
			$areaID = $this->parameters['venue']->getAreaId();
		} else if ($this->parameters['event']->getAreaId()) {
			$areaID = $this->parameters['event']->getAreaId();
		}
		
		if ($areaID) {	
			$ar = new AreaRepository();
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
		
		if ($this->parameters['event']->getImportUrlId()) {
			$iur = new ImportURLRepository();
			$this->parameters['importurl'] = $iur->loadById($this->parameters['event']->getImportUrlId());
		}
		
		$groupRB = new GroupRepositoryBuilder();
		$groupRB->setEvent($this->parameters['event']);
		$this->parameters['groups'] = $groupRB->fetchAll();
		if ($this->parameters['event']->getGroupId()) {
			foreach($this->parameters['groups'] as $group)  {
				if ($group->getId() == $this->parameters['event']->getGroupId()) {
					$this->parameters['group'] = $group;
				}
			}
		}
		
		
		return true;

	}

	function show($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
				
		if (userGetCurrent() && !$this->parameters['event']->isInPast()) {
			$uaer = new UserAtEventRepository();
			$this->parameters['userAtEvent'] = $uaer->loadByUserAndEventOrInstanciate(userGetCurrent(), $this->parameters['event']);
		}
		
		if (userGetCurrent()) {
			$clrb = new CuratedListRepositoryBuilder();
			$clrb->setSite($app['currentSite']);
			$clrb->setUserCanEdit(userGetCurrent());
			$clrb->setEventInformation($this->parameters['event']);
			$this->parameters['curatedListsUserCanEdit'] = $clrb->fetchAll();
		} else {
			$this->parameters['curatedListsUserCanEdit'] = array();
		}
		
		$this->parameters['mediasForGroup'] = array();
		$this->parameters['medias'] = array();
		foreach($this->parameters['groups'] as $group) {
			$mrb = new MediaRepositoryBuilder();
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setGroup($group);
			$this->parameters['mediasForGroup'][$group->getSlug()] = $mrb->fetchAll();
			$this->parameters['medias'] = array_merge($this->parameters['medias'],$this->parameters['mediasForGroup'][$group->getSlug()]);
		}
		
		if ($this->parameters['venue']) {
			$mrb = new MediaRepositoryBuilder();
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setVenue($this->parameters['venue']);
			$this->parameters['mediasForVenue'] = $mrb->fetchAll();
			$this->parameters['medias'] = array_merge($this->parameters['medias'],$this->parameters['mediasForVenue']);
		}
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingYesOnly(true);
		$uaerb->setPlanPublicOnly(true);
		$this->parameters['userAtEventYesPublic'] = $uaerb->fetchAll();
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingYesOnly(true);
		$uaerb->setPlanPrivateOnly(true);
		$this->parameters['userAtEventYesPrivate'] = $uaerb->fetchAll();
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingMaybeOnly(true);
		$uaerb->setPlanPublicOnly(true);
		$this->parameters['userAtEventMaybePublic'] = $uaerb->fetchAll();
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingMaybeOnly(true);
		$uaerb->setPlanPrivateOnly(true);
		$this->parameters['userAtEventMaybePrivate'] = $uaerb->fetchAll();
		
		if ($this->parameters['country']) {
			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setSite($app['currentSite']);
			$areaRepoBuilder->setCountry($this->parameters['country']);
			$areaRepoBuilder->setIncludeDeleted(false);
			if ($this->parameters['area']) {
				$areaRepoBuilder->setParentArea($this->parameters['area']);
			} else {
				$areaRepoBuilder->setNoParentArea(true);
			}
			$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();
		}
		
		return $app['twig']->render('site/event/show.html.twig', $this->parameters);
	}
	

	function userAttendanceHtml($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingYesOnly(true);
		$uaerb->setPlanPublicOnly(true);
		$this->parameters['userAtEventYesPublic'] = $uaerb->fetchAll();
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingYesOnly(true);
		$uaerb->setPlanPrivateOnly(true);
		$this->parameters['userAtEventYesPrivate'] = $uaerb->fetchAll();
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingMaybeOnly(true);
		$uaerb->setPlanPublicOnly(true);
		$this->parameters['userAtEventMaybePublic'] = $uaerb->fetchAll();
		
		$uaerb = new UserAtEventRepositoryBuilder();
		$uaerb->setEventOnly($this->parameters['event']);
		$uaerb->setPlanAttendingMaybeOnly(true);
		$uaerb->setPlanPrivateOnly(true);
		$this->parameters['userAtEventMaybePrivate'] = $uaerb->fetchAll();
		
		return $app['twig']->render('/site/event/usersAttending.html.twig', $this->parameters);
	}
	
	 /** This can be used by Js for both getting and setting values **/
	function myAttendanceJson($slug, Request $request, Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		$uaer = new UserAtEventRepository();
		$userAtEvent = $uaer->loadByUserAndEventOrInstanciate(userGetCurrent(), $this->parameters['event']);

		$data = array();
		
		if (isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken() && !$this->parameters['event']->isInPast()) {
			
			if (isset($_POST['privacy']) && $_POST['privacy'] == 'public') {
				$userAtEvent->setIsPlanPublic(true);
			} else if (isset($_POST['privacy']) && $_POST['privacy'] == 'private') {
				$userAtEvent->setIsPlanPublic(false);
			}
			
			if (isset($_POST['attending']) && $_POST['attending'] == 'no') {
				$userAtEvent->setIsPlanAttending(false);
				$userAtEvent->setIsPlanMaybeAttending(false);
			} else if (isset($_POST['attending']) && $_POST['attending'] == 'maybe') {
				$userAtEvent->setIsPlanAttending(false);
				$userAtEvent->setIsPlanMaybeAttending(true);
			} else if (isset($_POST['attending']) && $_POST['attending'] == 'yes') {
				$userAtEvent->setIsPlanAttending(true);
				$userAtEvent->setIsPlanMaybeAttending(false);
			}
			
			$uaer->save($userAtEvent);
		}

		$data['attending'] = ($userAtEvent->getIsPlanAttending() ? 'yes' : ($userAtEvent->getIsPlanMaybeAttending()?'maybe':'no'));
		$data['privacy'] = ($userAtEvent->getIsPlanPublic() ? 'public' : 'private');
		$data['inPast'] = $this->parameters['event']->isInPast() ? 1 : 0;
		$data['CSFRToken'] = $WEBSESSION->getCSFRToken();
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;	
	}
	
	function history($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

				
		$ehrb = new EventHistoryRepositoryBuilder();
		$ehrb->setEvent($this->parameters['event']);
		$this->parameters['eventHistories'] = $ehrb->fetchAll();
				
		return $app['twig']->render('site/event/history.html.twig', $this->parameters);
	}
	
	
	function edit($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		$timeZone = isset($_POST['EventEditForm']) && isset($_POST['EventEditForm']['timezone']) ? $_POST['EventEditForm']['timezone'] : $this->parameters['event']->getTimezone();
		if ($this->parameters['event']->getIsImported()) {
			$form = $app['form.factory']->create(new EventImportedEditForm($app['currentSite'], $timeZone), $this->parameters['event']);
		} else {
			$form = $app['form.factory']->create(new EventEditForm($app['currentSite'], $timeZone), $this->parameters['event']);
		}
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->edit($this->parameters['event'], userGetCurrent());
				
				return $app->redirect("/event/".$this->parameters['event']->getSlugForURL());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		if ($this->parameters['event']->getIsImported()) {
			return $app['twig']->render('site/event/edit.imported.html.twig', $this->parameters);
		} else {
			return $app['twig']->render('site/event/edit.html.twig', $this->parameters);
		}
		
	}
	
	
	function editVenue($slug, Request $request, Application $app) {
		global $CONFIG, $WEBSESSION;
		
		//var_dump($_POST); die();
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ('POST' == $request->getMethod() && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
				
			$venueRepository = new VenueRepository;
			$areaRepository = new AreaRepository();
			$countryRepository = new CountryRepository();

			if (isset($_POST['venue_id']) && $_POST['venue_id'] == 'new' && trim($_POST['newVenueTitle'])) {
				
				$area = null;
				if (isset($_POST['areas']) && is_array($_POST['areas'])) {
					foreach ($_POST['areas'] as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						} else if (substr($areaCode, 0, 4) == 'NEW:') {
							$newArea = new AreaModel();
							$newArea->setTitle(substr($areaCode, 4));
							$areaRepository->create($newArea, $area, $app['currentSite'], $this->parameters['country'] , userGetCurrent());
							$areaRepository->buildCacheAreaHasParent($newArea);
							$area = $newArea;
						}
					}
				}

				$venue = new VenueModel();
				$venue->setTitle($_POST['newVenueTitle']);
				$venue->setAddress($_POST['newVenueAddress']);
				$venue->setAddressCode($_POST['newVenueAddressCode']);
				$venue->setCountryId($this->parameters['country']->getId());
				if ($area) $venue->setAreaId($area->getId());
				
				$venueRepository->create($venue, $app['currentSite'], userGetCurrent());
				
				$this->parameters['event']->setVenueId($venue->getId());
				$eventRepository = new EventRepository();
				$eventRepository->edit($this->parameters['event'], userGetCurrent());
				return $app->redirect("/event/".$this->parameters['event']->getSlugForURL());
				
			} if (isset($_POST['venue_id']) && $_POST['venue_id'] == 'no') {
				
				$area = null;
				if (isset($_POST['areas']) && is_array($_POST['areas'])) {
					foreach ($_POST['areas'] as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						} else if (substr($areaCode, 0, 4) == 'NEW:') {
							$newArea = new AreaModel();
							$newArea->setTitle(substr($areaCode, 4));
							$areaRepository->create($newArea, $area, $app['currentSite'], $this->parameters['country'] , userGetCurrent());
							$areaRepository->buildCacheAreaHasParent($newArea);
							$area = $newArea;
						}
					}
				}
				
				if ($area) {
					$this->parameters['event']->setAreaId($area->getId());
				} else {
					$this->parameters['event']->setAreaId(null);
				}
				$this->parameters['event']->setVenueId(null);
				$eventRepository = new EventRepository();
				$eventRepository->edit($this->parameters['event'], userGetCurrent());
				return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
				
			} else if (isset($_POST['venue_id']) && intval($_POST['venue_id'])) {
				$venue = $venueRepository->loadBySlug($app['currentSite'], $_POST['venue_id']);
				if ($venue) {
					$this->parameters['event']->setVenueId($venue->getId());
					$eventRepository = new EventRepository();
					$eventRepository->edit($this->parameters['event'], userGetCurrent());
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
				}
			}
			
		}
		
		
		return $app['twig']->render('site/event/edit.venue.html.twig', $this->parameters);
		
	}
	
	
	function recur($slug, Request $request, Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}
		
		
		if (!$this->parameters['group']) {
			
			// Existing Group
			// TODO csfr
			if (isset($_POST['intoGroupSlug']) && $_POST['intoGroupSlug']) {
				$groupRepo = new GroupRepository();
				$group = $groupRepo->loadBySlug($app['currentSite'], $_POST['intoGroupSlug']);
				if ($group) {
					$groupRepo->addEventToGroup($this->parameters['event'], $group);
					$repo = new UserWatchesGroupRepository();
					$repo->startUserWatchingGroupIfNotWatchedBefore(userGetCurrent(), $group);
					return $app->redirect("/event/".$this->parameters['event']->getSlug()."/recur/");
				}
			}
			
			// New group
			if (isset($_POST['NewGroupTitle']) && $_POST['NewGroupTitle'] && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
				$group = new GroupModel();
				$group->setTitle($_POST['NewGroupTitle']);
				$groupRepo = new GroupRepository();
				$groupRepo->create($group, $app['currentSite'], userGetCurrent());
				$groupRepo->addEventToGroup($this->parameters['event'], $group);
				return $app->redirect("/event/".$this->parameters['event']->getSlug()."/recur/");
			}
			
			return $app['twig']->render('site/event/recur.groupneeded.html.twig', $this->parameters);
			
		} else {
			
			$eventRecurSet = new EventRecurSetModel();
			$eventRecurSet->setTimeZoneName($this->parameters['event']->getTimezone());
			$data = $eventRecurSet->getEventPatternData($this->parameters['event']);
			
			if ($data['weekInMonth'] < 5) {
				$this->parameters['recurMonthlyOnWeekNumber'] = $data['weekInMonth'];
				$ordinal = array(1 => 'st',2 => 'nd',3 => 'rd',4 => 'th');
				$this->parameters['recurMonthlyOnWeekNumberOrdinal'] = $ordinal[$data['weekInMonth']];
			} else {
				$this->parameters['recurMonthlyOnWeekNumber'] = null;
				$this->parameters['recurMonthlyOnWeekNumberOrdinal'] = null;
			}
			$this->parameters['recurMonthlyOnLastWeek'] = $data['isLastWeekInMonth'];
			
			return $app['twig']->render('site/event/recur.html.twig', $this->parameters);
		}
		
	}
	
	function recurWeekly($slug, Request $request, Application $app) {
		global $WEBSESSION;
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		if (!$this->parameters['group']) {
			die("NO");
		}
		
		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}
		
		$eventRecurSetRepository = new EventRecurSetRepository();
		$eventRecurSet = $eventRecurSetRepository->getForEvent($this->parameters['event']);
		
		$eventRecurSet->setTimeZoneName($app['currentTimeZone']);
		$this->parameters['newEvents'] = $eventRecurSet->getNewWeeklyEventsFilteredForExisting($this->parameters['event']);
		
		if (isset($_POST['submitted']) && $_POST['submitted'] == 'yes' && $WEBSESSION->getCSFRToken()) {
			
			$data = is_array($_POST['new']) ? $_POST['new'] : array();
			
			$eventRepository = new EventRepository();
			$count = 0;
			foreach($this->parameters['newEvents'] as $event) {
				if (in_array($event->getStartAt()->getTimeStamp(), $data)) {
					$eventRepository->create($event, $app['currentSite'], userGetCurrent(), $this->parameters['group'], $this->parameters['groups']);
					++$count;
				}
			}
			
			if ($count > 0) {
				return $app->redirect("/group/".$this->parameters['group']->getSlug());
			}
			
		}
		
		return $app['twig']->render('site/event/recurWeekly.html.twig', $this->parameters);
	}
	
	
	function recurMonthly($slug, Request $request, Application $app) {
		global $WEBSESSION;
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		if (!$this->parameters['group']) {
			die("NO");
		}
		
		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}
		
		$eventRecurSetRepository = new EventRecurSetRepository();
		$eventRecurSet = $eventRecurSetRepository->getForEvent($this->parameters['event']);
		
		$eventRecurSet->setTimeZoneName($app['currentTimeZone']);
		$this->parameters['newEvents'] = $eventRecurSet->getNewMonthlyEventsOnSetDayInWeekFilteredForExisting($this->parameters['event']);
		
		if (isset($_POST['submitted']) && $_POST['submitted'] == 'yes' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			
			$data = is_array($_POST['new']) ? $_POST['new'] : array();
			
			$eventRepository = new EventRepository();
			$count = 0;
			foreach($this->parameters['newEvents'] as $event) {
				if (in_array($event->getStartAt()->getTimeStamp(), $data)) {
					$eventRepository->create($event, $app['currentSite'], userGetCurrent(), $this->parameters['group'], $this->parameters['groups']);
					++$count;
				}
			}
			
			if ($count > 0) {
				return $app->redirect("/group/".$this->parameters['group']->getSlug());
			}
			
		}
		
		return $app['twig']->render('site/event/recurMonthly.html.twig', $this->parameters);
	}
	
	
	
	
	function recurMonthlyLast($slug, Request $request, Application $app) {
		global $WEBSESSION;
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		if (!$this->parameters['group']) {
			die("NO");
		}
		
		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}
		
		$eventRecurSetRepository = new EventRecurSetRepository();
		$eventRecurSet = $eventRecurSetRepository->getForEvent($this->parameters['event']);
		
		$eventRecurSet->setTimeZoneName($app['currentTimeZone']);
		$this->parameters['newEvents'] = $eventRecurSet->getNewMonthlyEventsOnLastDayInWeekFilteredForExisting($this->parameters['event']);
		
		if (isset($_POST['submitted']) && $_POST['submitted'] == 'yes' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			
			$data = is_array($_POST['new']) ? $_POST['new'] : array();
			
			$eventRepository = new EventRepository();
			$count = 0;
			foreach($this->parameters['newEvents'] as $event) {
				if (in_array($event->getStartAt()->getTimeStamp(), $data)) {
					$eventRepository->create($event, $app['currentSite'], userGetCurrent(), $this->parameters['group'], $this->parameters['groups']);
					++$count;
				}
			}
			
			if ($count > 0) {
				return $app->redirect("/group/".$this->parameters['group']->getSlug());
			}
			
		}
		
		return $app['twig']->render('site/event/recurMonthly.html.twig', $this->parameters);
	}
	
	
	
	function delete($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}
		
		$form = $app['form.factory']->create(new EventDeleteForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->delete($this->parameters['event'], userGetCurrent());
				
				return $app->redirect("/event/".$this->parameters['event']->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/event/delete.html.twig', $this->parameters);
		
	}
	
		
	function undelete($slug, Request $request, Application $app) {
			
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		//TODO It's inefective to get them all when really we just want the latest one!
		
		$ehrb = new EventHistoryRepositoryBuilder();
		$ehrb->setEvent($this->parameters['event']);
		$ehrb->setOrderByCreatedAt(true);
		$eventHistories = $ehrb->fetchAll();
		
		$eventHistory = $eventHistories[0];
		
		return $app->redirect("/event/".$this->parameters['event']->getSlug().'/rollback/'.$eventHistory->getCreatedAtTimeStamp());
		
		
	}
	
	
	function rollback($slug, $timestamp, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		
		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}
		
		$ehr = new EventHistoryRepository();
		$this->parameters['eventHistory'] = $ehr->loadByEventAndtimeStamp($this->parameters['event'], $timestamp);
		if (!$this->parameters['eventHistory']) {
			$app->abort(404, "Event History does not exist.");
		}
		
		$newEventState = clone $this->parameters['event'];
		$newEventState->setFromHistory($this->parameters['eventHistory']);
		
		$form = $app['form.factory']->create(new EventEditForm($app['currentSite'],$app['currentTimeZone']), $newEventState);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->edit($newEventState, userGetCurrent(), $this->parameters['eventHistory']);
				
				return $app->redirect("/event/".$this->parameters['event']->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/event/rollback.html.twig', $this->parameters);
		
		
	}
	
	
	function moveToArea($slug, Request $request, Application $app) {
		global $CONFIG, $FLASHMESSAGES, $WEBSESSION;
	
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		if (isset($_POST) && isset($_POST['area']) && isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken() && 
				!$this->parameters['venue']) {
			
			if ($_POST['area'] == 'new' && trim($_POST['newAreaTitle']) && $this->parameters['country']) {
				
				$area = new AreaModel();
				$area->setTitle(trim($_POST['newAreaTitle']));
				
				$areaRepository = new AreaRepository();
				$areaRepository->create($area, $this->parameters['area'], $app['currentSite'], $this->parameters['country'], userGetCurrent());
				
				$this->parameters['event']->setAreaId($area->getId());
				$eventRepository = new EventRepository();
				$eventRepository->edit($this->parameters['event'], userGetCurrent());
				
				$areaRepository->buildCacheAreaHasParent($area);
				
				$FLASHMESSAGES->addMessage('Thank you; event updated!');
				
			} elseif (intval($_POST['area'])) {
				
				$areaRepository = new AreaRepository();
				$area = $areaRepository->loadBySlug($app['currentSite'], $_POST['area']);
				if ($area) {
					$this->parameters['event']->setAreaId($area->getId());
					$eventRepository = new EventRepository();
					$eventRepository->edit($this->parameters['event'], userGetCurrent());
					$FLASHMESSAGES->addMessage('Thank you; event updated!');
				}
			
			}
			
		}
		
		return $app->redirect("/event/".$this->parameters['event']->getSlug().'/');

	}
	
}


