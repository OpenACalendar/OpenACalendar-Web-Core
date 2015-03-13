<?php

namespace site\controllers;

use repositories\builders\VenueRepositoryBuilder;
use Silex\Application;
use site\forms\EventNewForm;
use site\forms\EventEditForm;
use site\forms\EventImportedEditForm;
use site\forms\EventDeleteForm;
use site\forms\EventCancelForm;
use site\forms\UploadNewMediaForm;
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
use repositories\TagRepository;
use repositories\UserWatchesGroupRepository;
use repositories\MediaInEventRepository;
use repositories\MediaRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\EventHistoryRepositoryBuilder;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\UserAtEventRepositoryBuilder;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use repositories\builders\filterparams\GroupFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController {
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		global $CONFIG;

		$this->parameters = array(
			'group'=>null,
			'venue'=>null,
			'country'=>null,
			'area'=>null, 
			'parentAreas'=>array(), 
			'childAreas'=>array(),
			'importurl'=>null,
			'eventIsDuplicateOf'=>null,
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

		if ($this->parameters['event']->getIsDuplicateOfId()) {
			$this->parameters['eventIsDuplicateOf'] = $eventRepository->loadByID($this->parameters['event']->getIsDuplicateOfId());
		}

		$app['currentUserActions']->set("org.openacalendar","eventHistory",true);
		$app['currentUserActions']->set("org.openacalendar","eventEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsDeleted()
			&& !$this->parameters['event']->getIsCancelled());
		$app['currentUserActions']->set("org.openacalendar","eventEditVenue",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsDeleted()
			&& !$this->parameters['event']->getIsCancelled()
			&& $app['currentSite']->getIsFeaturePhysicalEvents());
		$app['currentUserActions']->set("org.openacalendar","eventEditTags",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsDeleted()
			&& !$this->parameters['event']->getIsCancelled()
			&& $app['currentSite']->getIsFeatureTag());
		$app['currentUserActions']->set("org.openacalendar","eventEditGroups",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsDeleted()
			&& !$this->parameters['event']->getIsCancelled()
			&& $app['currentSite']->getIsFeatureGroup());
		$app['currentUserActions']->set("org.openacalendar","eventEditMedia",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsDeleted()
			&& !$this->parameters['event']->getIsCancelled()
			&& $CONFIG->isFileStore());
		$app['currentUserActions']->set("org.openacalendar","eventRecur",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsImported()
			&& !$this->parameters['event']->getIsDeleted()
			&& !$this->parameters['event']->getIsCancelled());
		$app['currentUserActions']->set("org.openacalendar","eventDelete",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsImported()
			&& !$this->parameters['event']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","eventCancel",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsImported()
			&& !$this->parameters['event']->getIsCancelled()
			&& !$this->parameters['event']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","eventUndelete",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsImported()
			&& $this->parameters['event']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","eventUncancel",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& !$this->parameters['event']->getIsImported()
			&& $this->parameters['event']->getIsCancelled());

		return true;

	}

	protected function addTagsToParameters(Application $app) {
		if (!isset($this->parameters['tags'])) {
			if ($app['currentSite']->getIsFeatureTag()) {
				$trb = new TagRepositoryBuilder();
				$trb->setSite($app['currentSite']);
				$trb->setIncludeDeleted(false);
				$trb->setTagsForEvent($this->parameters['event']);
				$this->parameters['tags'] = $trb->fetchAll();
			} else {
				$this->parameters['tags'] = array();
			}
		}		
	}


	function show($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
				
		if ($app['currentUser'] && !$this->parameters['event']->isInPast()) {
			$uaer = new UserAtEventRepository();
			$this->parameters['userAtEvent'] = $uaer->loadByUserAndEventOrInstanciate($app['currentUser'], $this->parameters['event']);
		}

		$this->parameters['mediasForGroup'] = array();
		$this->parameters['mediasForVenue'] = array();
		$this->parameters['mediasForEvent'] = array();
		$this->parameters['medias'] = array();
		if ($app['config']->isFileStore()) {
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

			$mrb = new MediaRepositoryBuilder();
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setEvent($this->parameters['event']);
			$this->parameters['mediasForEvent'] = $mrb->fetchAll();
			$this->parameters['medias'] = array_merge($this->parameters['medias'],$this->parameters['mediasForEvent']);
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

		if ($app['currentUser']) {
			$uaer = new UserAtEventRepository();
			$uae = $uaer->loadByUserAndEvent($app['currentUser'], $this->parameters['event']);
			$this->parameters['userAtEventIsCurrentUser'] = $uae && ($uae->getIsPlanAttending() || $uae->getIsPlanMaybeAttending());
		} else {
			$this->parameters['userAtEventIsCurrentUser'] = false;
		}

		$this->addTagsToParameters($app);
		
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
			$app['currentUserActions']->set("org.openacalendar","eventEditPushToChildAreas",
				$this->parameters['childAreas'] &&
				$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
				&& $app['currentSite']->getIsFeaturePhysicalEvents()
				&& $this->parameters['event']->getIsPhysical()
				&& !$this->parameters['event']->getIsCancelled()
				&& !$this->parameters['event']->getIsDeleted());
		}

		$this->parameters['isGroupRunningOutOfFutureEvents'] = 0;
		if ($this->parameters['group'] &&
			!$this->parameters['group']->getIsDeleted()
			&& $app['currentSite']->getIsFeatureGroup()
			&& $app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
			$groupRepo = new GroupRepository();
			$this->parameters['isGroupRunningOutOfFutureEvents'] = $groupRepo->isGroupRunningOutOfFutureEvents($this->parameters['group'], $app['currentSite']);
		}



		$this->parameters['templatesAfterDetails'] = array();
		$this->parameters['templatesAtEnd'] = array();
		foreach($app['extensions']->getExtensions() as $extension) {
			foreach($extension->getAddContentToEventShowPages($this->parameters) as $addContent) {
				$this->parameters = array_merge($this->parameters, $addContent->getParameters());
				$this->parameters['templatesAfterDetails'] = array_merge($this->parameters['templatesAfterDetails'], $addContent->getTemplatesAfterDetails());
				$this->parameters['templatesAtEnd'] = array_merge($this->parameters['templatesAtEnd'], $addContent->getTemplatesAtEnd());
			}
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

		if ($app['currentUser']) {
			$uaer = new UserAtEventRepository();
			$uae = $uaer->loadByUserAndEvent($app['currentUser'], $this->parameters['event']);
			$this->parameters['userAtEventIsCurrentUser'] = $uae && ($uae->getIsPlanAttending() || $uae->getIsPlanMaybeAttending());
		} else {
			$this->parameters['userAtEventIsCurrentUser'] = false;
		}

		return $app['twig']->render('/site/event/usersAttending.html.twig', $this->parameters);
	}
	
	 /** This can be used by Js for both getting and setting values **/
	function myAttendanceJson($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		$uaer = new UserAtEventRepository();
		$userAtEvent = $uaer->loadByUserAndEventOrInstanciate($app['currentUser'], $this->parameters['event']);

		$data = array();
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken() && !$this->parameters['event']->isInPast()) {
			
			if ($request->request->get('privacy') == 'public') {
				$userAtEvent->setIsPlanPublic(true);
			} else if ($request->request->get('privacy') == 'private') {
				$userAtEvent->setIsPlanPublic(false);
			}
			
			if ($request->request->get('attending') == 'no') {
				$userAtEvent->setIsPlanAttending(false);
				$userAtEvent->setIsPlanMaybeAttending(false);
			} else if ($request->request->get('attending') == 'maybe') {
				$userAtEvent->setIsPlanAttending(false);
				$userAtEvent->setIsPlanMaybeAttending(true);
			} else if ($request->request->get('attending') == 'yes') {
				$userAtEvent->setIsPlanAttending(true);
				$userAtEvent->setIsPlanMaybeAttending(false);
			}
			
			$uaer->save($userAtEvent);
		}

		$data['attending'] = ($userAtEvent->getIsPlanAttending() ? 'yes' : ($userAtEvent->getIsPlanMaybeAttending()?'maybe':'no'));
		$data['privacy'] = ($userAtEvent->getIsPlanPublic() ? 'public' : 'private');
		$data['inPast'] = $this->parameters['event']->isInPast() ? 1 : 0;
		$data['CSFRToken'] = $app['websession']->getCSFRToken();
		
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
	
	function editSplash($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		return $app['twig']->render('site/event/edit.splash.html.twig', $this->parameters);

	}
	
	function editDetails($slug, Request $request, Application $app) {
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
			$form = $app['form.factory']->create(new EventEditForm($app['currentSite'], $timeZone, $app), $this->parameters['event']);
		}
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->edit($this->parameters['event'], $app['currentUser']);
				
				
				$repo = new EventRecurSetRepository();
				if ($repo->isEventInSetWithNotDeletedFutureEvents($this->parameters['event'])) {
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL().'/edit/future');
				} else {
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
				}
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		if ($this->parameters['event']->getIsImported()) {
			return $app['twig']->render('site/event/edit.details.imported.html.twig', $this->parameters);
		} else {
			return $app['twig']->render('site/event/edit.details.html.twig', $this->parameters);
		}
		
	}

	/**
	 * Event Edit Venue - remove "the" from start of search words to get more matches
	 * eg "Pear Tree" exists, user searches for "The Pear Tree"
	 *
	 * @param $in
	 * @return string
	 */
	protected function removeSearchPrefixWords($in) {
		if (strtolower(substr(trim($in),0,4)) == "the ") {
			return substr(trim($in), 4);
		} else {
			return $in;
		}
	}


	function editVenue($slug, Request $request, Application $app) {		
		//var_dump($_POST); die();
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}

		$areaRepository = new AreaRepository();

		$this->parameters['doesCountryHaveAnyNotDeletedAreas'] = $areaRepository->doesCountryHaveAnyNotDeletedAreas($app['currentSite'], $this->parameters['country']);
		$this->parameters['searchAddressCode'] = $request->query->get('searchAddressCode');
		$this->parameters['searchArea'] = $this->removeSearchPrefixWords($request->query->get('searchArea'));
		$this->parameters['searchAreaSlug'] = $request->query->get('searchAreaSlug');
		$this->parameters['searchAreaObject'] = $request->query->get('searchAreaObject');
		$this->parameters['searchAddress'] = $request->query->get('searchAddress');
		$this->parameters['searchTitle'] = $request->query->get('searchTitle');
		$this->parameters['searchFieldsSubmitted'] = $request->query->get('fieldsSubmitted') == '1';

		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {


			$venueRepository = new VenueRepository();


			if ($request->request->get('venue_slug') && intval($request->request->get('venue_slug'))) {
				// Intval() check here to make sure we have a int passed and not "no" or "new"
				$venue = $venueRepository->loadBySlug($app['currentSite'], intval($request->request->get('venue_slug')));
				if ($venue) {
					$this->parameters['event']->setVenueId($venue->getId());
					$this->parameters['event']->setAreaId(null);
					$eventRepository = new EventRepository();
					$eventRepository->edit($this->parameters['event'], $app['currentUser']);
					$gotResult = true;
				}
			}

			if ($gotResult) {
				$repo = new EventRecurSetRepository();
				if ($repo->isEventInSetWithNotDeletedFutureEvents($this->parameters['event'])) {
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL().'/edit/future');
				} else {
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
				}
			}
		}


		$this->editVenueGetDataIntoParameters($app);

		return $app['twig']->render('site/event/edit.venue.html.twig', $this->parameters);
		
	}

	function editVenueJson($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}

		$areaRepository = new AreaRepository();

		$this->parameters['doesCountryHaveAnyNotDeletedAreas'] = $areaRepository->doesCountryHaveAnyNotDeletedAreas($app['currentSite'], $this->parameters['country']);
		$this->parameters['searchAddressCode'] = $request->query->get('searchAddressCode');
		$this->parameters['searchArea'] = $this->removeSearchPrefixWords($request->query->get('searchArea'));
		$this->parameters['searchAreaSlug'] = $request->query->get('searchAreaSlug');
		$this->parameters['searchAreaObject'] = $request->query->get('searchAreaObject');
		$this->parameters['searchAddress'] = $request->query->get('searchAddress');
		$this->parameters['searchTitle'] = $request->query->get('searchTitle');
		$this->parameters['searchFieldsSubmitted'] = $request->query->get('fieldsSubmitted') == '1';


		$this->editVenueGetDataIntoParameters($app);

		$data = array(
			'venueSearchDone'=>$this->parameters['venueSearchDone'],
			'searchAreaSlug'=>$this->parameters['searchAreaSlug'],
			'venues'=>array(),
			'areas'=>array(),
		);

		foreach($this->parameters['venues'] as $venue) {
			$data['venues'][] = array(
				'slug'=>$venue->getSlug(),
				'title'=>$venue->getTitle(),
				'address'=>$venue->getAddress(),
				'addresscode'=>$venue->getAddressCode(),
				'lat'=>$venue->getLat(),
				'lng'=>$venue->getLng(),
			);
		}
		foreach($this->parameters['areas'] as $area) {
			$data['areas'][] = array(
				'slug'=>$area->getSlug(),
				'title'=>$area->getTitle(),
				'parent1title'=>$area->getParent1Title(),
			);
		}

		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;

	}



	protected function editVenueGetDataIntoParameters(Application $app) {
		$this->parameters['areas'] = array();
		$this->parameters['venues'] = array();
		$this->parameters['venueSearchDone'] = false;



		if ($this->parameters['doesCountryHaveAnyNotDeletedAreas']) {
			// Area search
			if ($this->parameters['searchArea']) {
				$arb = new AreaRepositoryBuilder();
				$arb->setIncludeDeleted(false);
				$arb->setIncludeParentLevels(1);
				$arb->setSite($app['currentSite']);
				$arb->setCountry($this->parameters['country']);
				$arb->setFreeTextSearch($this->parameters['searchArea']);
				$this->parameters['areas'] = $arb->fetchAll();
				if (count($this->parameters['areas']) == 1 && !$this->parameters['searchAreaSlug']) {
					$this->parameters['searchAreaSlug'] = $this->parameters['areas'][0]->getSlug();
					$this->parameters['searchAreaObject'] = $this->parameters['areas'][0];
				}

				// has user selected a area and is it still in search results? If so select it.
				if (!$this->parameters['searchAreaObject'] && $this->parameters['searchAreaSlug'] && intval($this->parameters['searchAreaSlug'])) {
					foreach($this->parameters['areas'] as $area) {
						if ($area->getSlug() == $this->parameters['searchAreaSlug']) {
							$this->parameters['searchAreaObject'] = $area;
						}
					}
				}
			}
		}

		// If user has not added any search fields. and the event is in a area. let's search by area by default.
		if (!$this->parameters['searchFieldsSubmitted'] && !$this->parameters['searchAreaObject'] && $this->parameters['area']) {
			$this->parameters['searchAreaObject'] = $this->parameters['area'];
			$this->parameters['searchArea'] = $this->parameters['area']->getTitle();
		}


		// venue search
		if ($this->parameters['searchAddressCode'] || $this->parameters['searchAddress'] || $this->parameters['searchTitle'] || $this->parameters['searchAreaObject']) {
			$vrb = new VenueRepositoryBuilder();
			$vrb->setSite($app['currentSite']);
			$vrb->setCountry($this->parameters['country']);
			$vrb->setIncludeDeleted(false);
			if ($this->parameters['searchTitle']) {
				$vrb->setFreeTextSearchTitle($this->parameters['searchTitle']);
			}
			if ($this->parameters['searchAddress']) {
				$vrb->setFreeTextSearchAddress($this->parameters['searchAddress']);
			}
			if ($this->parameters['searchAddressCode']) {
				$vrb->setFreeTextSearchAddressCode($this->parameters['searchAddressCode']);
			}
			if ($this->parameters['searchAreaObject']) {
				$vrb->setArea($this->parameters['searchAreaObject']);
			}
			$this->parameters['venues'] = $vrb->fetchAll();
			$this->parameters['venueSearchDone'] = true;
		}

	}

	function editArea($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}

		// Firstly, are there any areas in this country? If not, this whole page is useless.
		$areaRepository = new AreaRepository();
		if (!$areaRepository->doesCountryHaveAnyNotDeletedAreas($app['currentSite'], $this->parameters['country'])) {
			return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
		}

		// There are areas. Continue with the workflow
		$this->parameters['search'] = $this->removeSearchPrefixWords($request->query->get('search'));

		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {

			$gotResult = false;

			if ($request->request->get('area_slug') && intval($request->request->get('area_slug'))) {
				// Intval() check here to make sure we have a int passed and not "no" or "new"
				$area = $areaRepository->loadBySlug($app['currentSite'], intval($request->request->get('area_slug')));
				if ($area) {
					$this->parameters['event']->setVenueId(null);
					$this->parameters['event']->setAreaId($area->getId());
					$eventRepository = new EventRepository();
					$eventRepository->edit($this->parameters['event'], $app['currentUser']);
					$gotResult = true;
				}
			}

			if ($gotResult) {
				$repo = new EventRecurSetRepository();
				if ($repo->isEventInSetWithNotDeletedFutureEvents($this->parameters['event'])) {
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL().'/edit/future');
				} else {
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
				}
			}
		}

		$this->editAreaGetDataIntoParameters($app);

		return $app['twig']->render('site/event/edit.area.html.twig', $this->parameters);

	}


	function editAreaJson($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		$this->parameters['search'] = $this->removeSearchPrefixWords($request->query->get('search'));

		$this->editAreaGetDataIntoParameters($app);

		$data = array(
			'areas'=>array(),
		);

		foreach($this->parameters['areas'] as $area) {
			$data['areas'][] = array(
				'slug'=>$area->getSlug(),
				'title'=>$area->getTitle(),
				'parent1title'=>$area->getParent1Title(),
				'minLat'=>$area->getMinLat(),
				'maxLat'=>$area->getMaxLat(),
				'minLng'=>$area->getMinLng(),
				'maxLng'=>$area->getMaxLng(),
			);
		}

		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;

	}

	protected function editAreaGetDataIntoParameters(Application $app) {
		$this->parameters['areas'] = array();
		$this->parameters['areaSearchDone'] = false;

		$arb = new AreaRepositoryBuilder();
		$arb->setSite($app['currentSite']);
		$arb->setCountry($this->parameters['country']);
		$arb->setIncludeDeleted(false);
		$arb->setIncludeParentLevels(1);
		if ($this->parameters['search']) { $arb->setFreeTextSearch($this->parameters['search']); }
		$this->parameters['areas'] = $arb->fetchAll();
		$this->parameters['areaSearchDone'] = true;

	}

	function editVenueNew($slug, Request $request, Application $app) {
		//var_dump($_POST); die();

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}

		$areaRepository = new AreaRepository();

		$this->parameters['shouldWeAskForArea'] = $app['currentSite']->getIsFeaturePhysicalEvents() &&
			$areaRepository->doesCountryHaveAnyNotDeletedAreas($app['currentSite'], $this->parameters['country']);

		$this->parameters['newVenueFieldsSubmitted'] = (boolean)('POST' == $request->getMethod() && $request->request->get('newVenueFieldsSubmitted'));

		//=====================================  Set Venue Object as Much as possible from what user passed!

		$this->parameters['venue'] = new VenueModel() ;
		$this->parameters['venue']->setTitle(('POST' == $request->getMethod()) ? $request->request->get('fieldTitle') : $request->query->get('fieldTitle'));
		$this->parameters['venue']->setDescription(('POST' == $request->getMethod()) ? $request->request->get('fieldDescription') : $request->query->get('fieldDescription'));
		$this->parameters['venue']->setAddress(('POST' == $request->getMethod()) ? $request->request->get('fieldAddress') : $request->query->get('fieldAddress'));
		$this->parameters['venue']->setAddressCode(('POST' == $request->getMethod()) ? $request->request->get('fieldAddressCode') : $request->query->get('fieldAddressCode'));
		$this->parameters['venue']->setCountryId($this->parameters['country']->getId());
		$this->parameters['venue']->setLat(('POST' == $request->getMethod()) ? $request->request->get('fieldLat') : $request->query->get('fieldLat'));
		$this->parameters['venue']->setLng(('POST' == $request->getMethod()) ? $request->request->get('fieldLng') : $request->query->get('fieldLng'));

		$this->parameters['fieldAreaObject'] = null;
		$this->parameters['noneOfAboveSelected'] = false;
		$this->parameters['areasToSelectSearch'] = false;
		$this->parameters['areasToSelectChildren'] = false;

		if ($this->parameters['shouldWeAskForArea']) {

			// has area already been passed?
			$this->parameters['fieldAreaSearchText'] = ('POST' == $request->getMethod()) ? $request->request->get('fieldAreaSearchText') : $request->query->get('fieldAreaSearchText');

			$this->parameters['fieldAreaSlug'] = ('POST' == $request->getMethod()) ? $request->request->get('fieldAreaSlug') : $request->query->get('fieldAreaSlug');
			$this->parameters['fieldAreaSlugSelected'] = ('POST' == $request->getMethod()) ? $request->request->get('fieldAreaSlugSelected') : null;

			// Did the user select a area from a list?
			// -1 indicates skip, must check for that
			// we must check this before we check fieldAreaSlug so that this can override fieldAreaSlug
			if ($this->parameters['fieldAreaSlugSelected']) {
				$fass = intval($this->parameters['fieldAreaSlugSelected']);
				if ($fass == -1) {
					$this->parameters['noneOfAboveSelected'] = true;
				} elseif($fass > 0) {
					$this->parameters['fieldAreaObject'] =  $areaRepository->loadBySlug($app['currentSite'], $fass);
				}
			}

			// Slug passed?
			// -1 indicates skip, must check for that
			if (!$this->parameters['fieldAreaObject'] && $this->parameters['fieldAreaSlug']) {
				$fas = intval($this->parameters['fieldAreaSlug']);
				if ($fas == -1) {
					$this->parameters['noneOfAboveSelected'] = true;
				} else if ($fas > 0) {
					$this->parameters['fieldAreaObject'] = $areaRepository->loadBySlug($app['currentSite'], $fas);
					if ($this->parameters['fieldAreaObject']) {
						$this->parameters['fieldArea'] =  $this->parameters['fieldAreaObject']->getTitle();
					}
				}
			}

			// Free text search string passed that only has 1 result?
			if (!$this->parameters['fieldAreaObject'] && $this->parameters['fieldAreaSearchText']) {
				$arb = new AreaRepositoryBuilder();
				$arb->setSite($app['currentSite']);
				$arb->setCountry($this->parameters['country']);
				$arb->setFreeTextSearch($this->parameters['fieldAreaSearchText']);
				$arb->setIncludeParentLevels(1);
				$this->parameters['areasToSelectSearch'] = $arb->fetchAll();
				if (count($this->parameters['areasToSelectSearch']) == 1) {
					$this->parameters['fieldAreaObject'] = $this->parameters['areasToSelectSearch'][0];
				}
			}

		}

		//=====================================  Call out to extensions to add details


		// Slightly ackward we have to set Area ID on venue object, then when extensions have done we need to reload the area object again.
		if ($this->parameters['fieldAreaObject']) {
			$this->parameters['venue']->setAreaId($this->parameters['fieldAreaObject']->getId());
		}
		foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
			$extension->addDetailsToVenue($this->parameters['venue']);
		}
		if ($this->parameters['venue']->getAreaId() &&
			(!$this->parameters['fieldAreaObject'] || $this->parameters['fieldAreaObject']->getId() != $this->parameters['venue']->getAreaId())) {
			$this->parameters['fieldAreaObject'] = $areaRepository->loadById($this->parameters['venue']->getAreaId());
		}

		//===================================== User gets to add details?

		// Check newVenueFieldsSubmitted because we always show the fields to user once
		// also title is a required field.

		if (!$this->parameters['newVenueFieldsSubmitted'] || !trim($this->parameters['venue']->getTitle())) {
			return $app['twig']->render('site/event/edit.venue.new.html.twig', $this->parameters);
		}

		//===================================== Do we prompt the user for more?

		if ($this->parameters['shouldWeAskForArea']) {

			// Did user type in text we had multiple options for?
			if (!$this->parameters['fieldAreaObject'] && $this->parameters['fieldAreaSearchText'] && !$this->parameters['noneOfAboveSelected']
				&& count($this->parameters['areasToSelectSearch']) > 1) {
				return $app['twig']->render('site/event/edit.venue.new.area.html.twig', $this->parameters);
			}

			// Child areas?
			// -1 indicates "none of the above", so don't prompt the user again.
			if (!$this->parameters['noneOfAboveSelected']) {
				$areaRepoBuilder = new AreaRepositoryBuilder();
				$areaRepoBuilder->setSite($app['currentSite']);
				$areaRepoBuilder->setCountry($this->parameters['country']);
				$areaRepoBuilder->setIncludeDeleted(false);
				$areaRepoBuilder->setIncludeParentLevels(1);
				if ($this->parameters['fieldAreaObject']) {
					$areaRepoBuilder->setParentArea($this->parameters['fieldAreaObject']);
				} else {
					$areaRepoBuilder->setNoParentArea(true);
				}
				$childAreas = $areaRepoBuilder->fetchAll();
				if ($childAreas) {
					$this->parameters['areasToSelectChildren'] = $childAreas;
					return $app['twig']->render('site/event/edit.venue.new.area.html.twig', $this->parameters);
				}
			}


		}

		//===================================== No prompt? We can  save!

		$venueRepository = new VenueRepository();
		$venueRepository->create($this->parameters['venue'], $app['currentSite'], $app['currentUser']);

		$this->parameters['event']->setVenueId($this->parameters['venue']->getId());
		$this->parameters['event']->setAreaId(null);
		$eventRepository = new EventRepository();
		$eventRepository->edit($this->parameters['event'], $app['currentUser']);

		$repo = new EventRecurSetRepository();
		if ($repo->isEventInSetWithNotDeletedFutureEvents($this->parameters['event'])) {
			return $app->redirect("/event/".$this->parameters['event']->getSlugforURL().'/edit/future');
		} else {
			return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
		}

	}


	function editFuture($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		// Event Recur Set
		$eventRecurSetRepo = new EventRecurSetRepository();
		$this->parameters['eventRecurSet'] = $eventRecurSetRepo->loadForEvent($this->parameters['event']);
		if (!$this->parameters['eventRecurSet']) {
			return false; // TODO
		}
		
		// Load history we are working with
		$eventHistoryRepo = new EventHistoryRepository();
		$this->parameters['eventHistory'] = $eventHistoryRepo->loadByEventAndlastEditByUser($this->parameters['event'], $app['currentUser']);
		if (!$this->parameters['eventHistory']) {
			return false; // TODO
		}
		$eventHistoryRepo->ensureChangedFlagsAreSet($this->parameters['eventHistory']);
		$this->parameters['eventRecurSet']->setInitalEventLastChange($this->parameters['eventHistory']);

		// load event before this edit
		$eventRepo = new EventRepository();
		$this->parameters['eventRecurSet']->setInitialEventJustBeforeLastChange($eventRepo->loadEventJustBeforeEdit($this->parameters['event'], $this->parameters['eventHistory']));
		
		// Event & Future Events
		$this->parameters['eventRecurSet']->setInitalEvent($this->parameters['event']);
		$eventRB = new EventRepositoryBuilder();
		$eventRB->setStartAfter($this->parameters['event']->getStartAtInUTC());
		$eventRB->setInSameRecurEventSet($this->parameters['event']);
		$eventRB->setIncludeDeleted(false);
		$this->parameters['eventRecurSet']->setFutureEvents($eventRB->fetchAll());
		if (!$this->parameters['eventRecurSet']->getFutureEvents()) {
			return false; // TODO
		}		
		
		// Let's check for upgrades, then apply or show user
		$this->parameters['eventRecurSet']->applyChangeToFutureEvents();
		
		if ($this->parameters['eventRecurSet']->isAnyProposedChangesPossible()) {
		
			if ($request->request->get('submitted') == 'cancel' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
			}
			
			if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				
				$eventRepo = new EventRepository();
				
				$countEvents = 0;
				foreach($this->parameters['eventRecurSet']->getFutureEvents() as $futureEvent) {
					
					$proposedChanges = $this->parameters['eventRecurSet']->getFutureEventsProposedChangesForEventSlug($futureEvent->getSlug());
					if ($proposedChanges->getSummaryChangePossible()) {
						$proposedChanges->setSummaryChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldSummary') == 1);
					} 
					
					if ($proposedChanges->getDescriptionChangePossible()) {
						$proposedChanges->setDescriptionChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldDescription') == 1);
					} 
					if ($proposedChanges->getCountryAreaVenueIdChangePossible()) {
						$proposedChanges->setCountryAreaVenueIdChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldCountryAreaVenue') == 1);
					} 
					if ($proposedChanges->getTimezoneChangePossible()) {
						$proposedChanges->setTimezoneChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldTimezone') == 1);
					} 
					if ($proposedChanges->getUrlChangePossible()) {
						$proposedChanges->setUrlChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldUrl') == 1);
					} 
					if ($proposedChanges->getTicketUrlChangePossible()) {
						$proposedChanges->setTicketUrlChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldTicketUrl') == 1);
					} 
					if ($proposedChanges->getIsVirtualChangePossible()) {
						$proposedChanges->setIsVirtualChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldIsVirtual') == 1);
					} 
					if ($proposedChanges->getIsPhysicalChangePossible()) {
						$proposedChanges->setIsPhysicalChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldIsPhysical') == 1);
					}
					if ($proposedChanges->getIsCancelledChangePossible()) {
						$proposedChanges->setIsCancelledChangeSelected($request->request->get("eventSlug".$futureEvent->getSlug().'fieldIsCancelled') == 1);
					}
					if ($proposedChanges->getStartEndAtChangePossible()) {
						$proposedChanges->setStartEndAtChangePossible($request->request->get("eventSlug".$futureEvent->getSlug().'fieldStartEnd') == 1);
					} 
					if ($proposedChanges->applyToEvent($futureEvent, $this->parameters['event'])) {
						$eventRepo->edit($futureEvent, $app['currentUser'], $this->parameters['eventHistory']);
						$countEvents++;
					}
				}

				if ($countEvents > 0) {
					$app['flashmessages']->addMessage($countEvents > 1 ? $countEvents . " future events edited." : "Future event edited.");
					return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
				}
				
			}
			
			// Only pass $futureEvent to the view layer if there are actually changes that can be made.
			$futureEvents = array();
			foreach($this->parameters['eventRecurSet']->getFutureEvents() as $futureEvent) {
				if ($this->parameters['eventRecurSet']->getFutureEventsProposedChangesForEventSlug($futureEvent->getSlug())->isAnyChangesPossible()) {
					$futureEvents[] = $futureEvent;
				}
			}
			$this->parameters['futureEvents'] = $futureEvents;
			$this->parameters['futureEventsProposedChanges'] = $this->parameters['eventRecurSet']->getFutureEventsProposedChanges();
			
			return $app['twig']->render('site/event/edit.future.html.twig', $this->parameters);
			
			
		} else {
			return $app->redirect("/event/".$this->parameters['event']->getSlugforURL());
		}
		
	}


	function recur($slug, Request $request, Application $app) {		
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
			if ($request->request->get('intoGroupSlug')) {
				$groupRepo = new GroupRepository();
				$group = $groupRepo->loadBySlug($app['currentSite'], $request->request->get('intoGroupSlug'));
				if ($group) {
					$groupRepo->addEventToGroup($this->parameters['event'], $group);
					$repo = new UserWatchesGroupRepository();
					$repo->startUserWatchingGroupIfNotWatchedBefore($app['currentUser'], $group);
					return $app->redirect("/event/".$this->parameters['event']->getSlugForURL()."/recur/");
				}
			}
			
			// New group
			if ($request->request->get('NewGroupTitle') && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				$group = new GroupModel();
				$group->setTitle($request->request->get('NewGroupTitle'));
				$groupRepo = new GroupRepository();
				$groupRepo->create($group, $app['currentSite'], $app['currentUser']);
				$groupRepo->addEventToGroup($this->parameters['event'], $group);
				return $app->redirect("/event/".$this->parameters['event']->getSlugForURL()."/recur/");
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
		$this->parameters['newEvents'] = $eventRecurSet->getNewWeeklyEventsFilteredForExisting($this->parameters['event'], $app['config']->recurEventForDaysInFutureWhenWeekly);
		
		if ($request->request->get('submitted') == 'yes' && $app['websession']->getCSFRToken() == $app['websession']->getCSFRToken()) {
			
			$data = is_array($request->request->get('new')) ? $request->request->get('new') : array();
			
			$this->addTagsToParameters($app);

			$mrb = new MediaRepositoryBuilder();
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setEvent($this->parameters['event']);
			$medias = $mrb->fetchAll();

			$eventRepository = new EventRepository();
			$count = 0;
			foreach($this->parameters['newEvents'] as $event) {
				if (in_array($event->getStartAt()->getTimeStamp(), $data)) {
					$eventRepository->create($event, $app['currentSite'], $app['currentUser'], $this->parameters['group'],
							$this->parameters['groups'], null, $this->parameters['tags'], $medias);
					++$count;
				}
			}
			
			if ($count > 0) {
				return $app->redirect("/group/".$this->parameters['group']->getSlugForURL());
			}
			
		}
		
		return $app['twig']->render('site/event/recurWeekly.html.twig', $this->parameters);
	}
	
	
	function recurMonthly($slug, Request $request, Application $app) {
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
		$this->parameters['newEvents'] = $eventRecurSet->getNewMonthlyEventsOnSetDayInWeekFilteredForExisting($this->parameters['event'], $app['config']->recurEventForDaysInFutureWhenMonthly);
		
		if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
			$data = is_array($request->request->get('new')) ? $request->request->get('new') : array();
			
			$this->addTagsToParameters($app);

			$mrb = new MediaRepositoryBuilder();
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setEvent($this->parameters['event']);
			$medias = $mrb->fetchAll();
			
			$eventRepository = new EventRepository();
			$count = 0;
			foreach($this->parameters['newEvents'] as $event) {
				if (in_array($event->getStartAt()->getTimeStamp(), $data)) {
					$eventRepository->create($event, $app['currentSite'], $app['currentUser'], $this->parameters['group'],
							$this->parameters['groups'], null, $this->parameters['tags'], $medias);
					++$count;
				}
			}
			
			if ($count > 0) {
				return $app->redirect("/group/".$this->parameters['group']->getSlugForURL());
			}
			
		}
		
		return $app['twig']->render('site/event/recurMonthly.html.twig', $this->parameters);
	}
	
	
	
	
	function recurMonthlyLast($slug, Request $request, Application $app) {
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
		$this->parameters['newEvents'] = $eventRecurSet->getNewMonthlyEventsOnLastDayInWeekFilteredForExisting($this->parameters['event'], $app['config']->recurEventForDaysInFutureWhenMonthly);
		
		if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
			$data = is_array($request->request->get('new')) ? $request->request->get('new') : array();

			$this->addTagsToParameters($app);

			$mrb = new MediaRepositoryBuilder();
			$mrb->setIncludeDeleted(false);
			$mrb->setSite($app['currentSite']);
			$mrb->setEvent($this->parameters['event']);
			$medias = $mrb->fetchAll();

			$eventRepository = new EventRepository();
			$count = 0;
			foreach($this->parameters['newEvents'] as $event) {
				if (in_array($event->getStartAt()->getTimeStamp(), $data)) {
					$eventRepository->create($event, $app['currentSite'], $app['currentUser'], $this->parameters['group'], $this->parameters['groups'],
						null, $this->parameters['tags'], $medias);
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
				$eventRepository->delete($this->parameters['event'], $app['currentUser']);

				return $app->redirect("/event/".$this->parameters['event']->getSlugForURL());
				
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
		
		return $app->redirect("/event/".$this->parameters['event']->getSlugforURL().'/rollback/'.$eventHistory->getCreatedAtTimeStamp());
		
		
	}

	function cancel($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}

		if ($this->parameters['event']->getIsCancelled()) {
			die("No"); // TODO
		}

		if ($this->parameters['event']->getIsImported()) {
			die("No"); // TODO
		}

		$form = $app['form.factory']->create(new EventCancelForm());

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				$eventRepository = new EventRepository();
				$eventRepository->cancel($this->parameters['event'], $app['currentUser']);

				$repo = new EventRecurSetRepository();
				if ($repo->isEventInSetWithNotDeletedFutureEvents($this->parameters['event'])) {
					return $app->redirect("/event/".$this->parameters['event']->getSlugForUrl().'/edit/future');
				} else {
					return $app->redirect("/event/".$this->parameters['event']->getSlugForUrl());
				}

			}
		}

		$this->parameters['form'] = $form->createView();

		return $app['twig']->render('site/event/cancel.html.twig', $this->parameters);

	}


	function uncancel($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		//TODO It's inefective to get them all when really we just want the latest one!

		$ehrb = new EventHistoryRepositoryBuilder();
		$ehrb->setEvent($this->parameters['event']);
		$ehrb->setOrderByCreatedAt(true);
		$eventHistories = $ehrb->fetchAll();

		$eventHistory = $eventHistories[0];

		return $app->redirect("/event/".$this->parameters['event']->getSlugForURL().'/rollback/'.$eventHistory->getCreatedAtTimeStamp());


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
		
		$form = $app['form.factory']->create(new EventEditForm($app['currentSite'],$app['currentTimeZone'], $app), $newEventState);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				// Because to undelete or uncancel something, you rollback to a valid state, when you rollback you must set these.
				$newEventState->setIsCancelled(false);
				$newEventState->setIsDeleted(false);

				$eventRepository = new EventRepository();
				$eventRepository->edit($newEventState, $app['currentUser'], $this->parameters['eventHistory']);
				
				return $app->redirect("/event/".$this->parameters['event']->getSlugForURL());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/event/rollback.html.twig', $this->parameters);
		
		
	}
	
	
	function moveToArea($slug, Request $request, Application $app) {	
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		$gotResultEditedVenue = false;
		$gotResultEditedEvent = false;

		if ($request->request->get('area') && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
			if (intval($request->request->get('area'))) {
				
				$areaRepository = new AreaRepository();
				$area = $areaRepository->loadBySlug($app['currentSite'], $request->request->get('area'));
				if ($area && (!$this->parameters['area'] || $area->getId() != $this->parameters['area']->getId())) {
					if ($this->parameters['venue']) {
						$this->parameters['venue']->setAreaId($area->getId());
						$venueRepository = new VenueRepository();
						$venueRepository->edit($this->parameters['venue'], $app['currentUser']);
						$gotResultEditedVenue = true;
					} else {
						$this->parameters['event']->setAreaId($area->getId());
						$eventRepository = new EventRepository();
						$eventRepository->edit($this->parameters['event'], $app['currentUser']);
						$gotResultEditedEvent = true;
					}
					$app['flashmessages']->addMessage('Thank you; event updated!');

				}
							
			}
			
		}
		
		if ($gotResultEditedEvent) {
			$repo = new EventRecurSetRepository();
			if ($repo->isEventInSetWithNotDeletedFutureEvents($this->parameters['event'])) {
				return $app->redirect("/event/".$this->parameters['event']->getSlugForUrl().'/edit/future');
			} else {
				return $app->redirect("/event/".$this->parameters['event']->getSlugForUrl());
			}
		} else {
			return $app->redirect("/event/".$this->parameters['event']->getSlugForUrl().'/');
		}

	}
	
	
	function editTags($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		$tagRepo = new TagRepository();
			
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
			if ($request->request->get('addTag')) {
				$tag = $tagRepo->loadBySlug($app['currentSite'], $request->request->get('addTag'));
				if ($tag) {
					$tagRepo->addTagToEvent($tag, $this->parameters['event'], $app['currentUser']);
				}
			} elseif ($request->request->get('removeTag')) {
				$tag = $tagRepo->loadBySlug($app['currentSite'], $request->request->get('removeTag'));
				if ($tag) {
					$tagRepo->removeTagFromEvent($tag, $this->parameters['event'], $app['currentUser']);
				}
				
			}
		
		
		}
		
		$trb = new TagRepositoryBuilder();
		$trb->setSite($app['currentSite']);
		$trb->setIncludeDeleted(false);
		$trb->setTagsForEvent($this->parameters['event']);
		$this->parameters['tags'] = $trb->fetchAll();
		
		$trb = new TagRepositoryBuilder();
		$trb->setSite($app['currentSite']);
		$trb->setIncludeDeleted(false);
		$trb->setTagsNotForEvent($this->parameters['event']);
		$this->parameters['tagsToAdd'] = $trb->fetchAll();

		return $app['twig']->render('site/event/edit.tags.html.twig', $this->parameters);
	}
	
	function editGroups($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		$groupRepo = new GroupRepository();
			
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
			if ($request->request->get('addGroup')) {
				$group = $groupRepo->loadBySlug($app['currentSite'], $request->request->get('addGroup'));
				if ($group) {
					$groupRepo->addEventToGroup($this->parameters['event'], $group, $app['currentUser']);
					// Need to redirect here so other parts of page are correct when shown
					return $app->redirect("/event/".$this->parameters['event']->getSlugForURL().'/edit/groups');
				}
			} elseif ($request->request->get('removeGroup')) {
				$group = $groupRepo->loadBySlug($app['currentSite'], $request->request->get('removeGroup'));
				if ($group) {
					$groupRepo->removeEventFromGroup($this->parameters['event'], $group, $app['currentUser']);
					// Need to redirect here so other parts of page are correct when shown
					return $app->redirect("/event/".$this->parameters['event']->getSlugForURL().'/edit/groups');
				}
				
			}
		
		}
		
		// TODO not ones already added.
		$this->parameters['groupListFilterParams'] = new GroupFilterParams();
		$this->parameters['groupListFilterParams']->set($_GET);
		$this->parameters['groupListFilterParams']->getGroupRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['groupListFilterParams']->getGroupRepositoryBuilder()->setIncludeDeleted(false);
		$this->parameters['groupListFilterParams']->getGroupRepositoryBuilder()->setLimit(30);
		$this->parameters['groupsToAdd'] = $this->parameters['groupListFilterParams']->getGroupRepositoryBuilder()->fetchAll();

		return $app['twig']->render('site/event/edit.groups.html.twig', $this->parameters);
	}
	
	
	function editMedia($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		if ($app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
			
			
			$form = $app['form.factory']->create(new UploadNewMediaForm($this->parameters['event']->getSummaryDisplay()));
		
			
			if ('POST' == $request->getMethod()) {
				$form->bind($request);

				if ($form->isValid() && $form['media']->getData()) {

					$mediaRepository = new MediaRepository();
					$media = $mediaRepository->createFromFile($form['media']->getData(), $app['currentSite'], $app['currentUser'],
							$form['title']->getData(),$form['source_text']->getData(),$form['source_url']->getData());
					
					if ($media) {

						$mediaInEventRepo = new MediaInEventRepository();
						$mediaInEventRepo->add($media, $this->parameters['event'], $app['currentUser']);
						
						$app['flashmessages']->addMessage('Picture added!');
						return $app->redirect("/event/".$this->parameters['event']->getSlugForURL());
						
					}
					
				}
			}
			$this->parameters['uploadNewMediaForm'] = $form->createView();
			
		}
		
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setEvent($this->parameters['event']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/event/edit.media.html.twig', $this->parameters);
	}
	
	function editMediaRemove($slug, $mediaslug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $mediaslug);
			if ($media) {
				$mediaInEventRepo = new MediaInEventRepository();
				$mediaInEventRepo->remove($media, $this->parameters['event'], $app['currentUser']);
				$app['flashmessages']->addMessage('Removed!');
			}
		}
		
		return $app->redirect("/event/".$this->parameters['event']->getSlugForURL().'/edit/media');
	}
	
	function editMediaAddExisting($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}
			
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $request->request->get('addMedia'));
			if ($media) {
				$mediaInEventRepo = new MediaInEventRepository();
				$mediaInEventRepo->add($media, $this->parameters['event'], $app['currentUser']);
				$app['flashmessages']->addMessage('Added!');
				return $app->redirect("/event/".$this->parameters['event']->getSlugForURL().'/');
			}
		}
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setNotInEvent($this->parameters['event']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/event/edit.media.add.existing.html.twig', $this->parameters);
	}

	function exportExistingGoogleCalendar($slug, Request $request, Application $app) {
		global $CONFIG;

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		// Apparently maximum safe length of a URL is 2000 chars.
		// Allocate 1000 to description,
		// 300 to address, 300 to title
		// rest for misc.

		$eventURL = $CONFIG->getWebSiteDomainSecure($app['currentSite']->getSlug()).'/event/'.$this->parameters['event']->getSlugForURL();

		$locationStringBits = array();
		if ($this->parameters['venue']) {
			if ($this->parameters['venue']->getTitle()) {
				$locationStringBits[] = $this->parameters['venue']->getTitle();
			}
			if ($this->parameters['venue']->getAddress()) {
				$locationStringBits[] = $this->parameters['venue']->getAddress();
			}
			if ($this->parameters['venue']->getAddressCode()) {
				$locationStringBits[] = $this->parameters['venue']->getAddressCode();
			}
		}
		if ($this->parameters['area']) {
			$locationStringBits[] = $this->parameters['area']->getTitle();
		}
		if ($this->parameters['country']) {
			$locationStringBits[] = $this->parameters['country']->getTitle();
		}

		$start = $this->parameters['event']->getStartAtInUTC();
		$end = $this->parameters['event']->getEndAtInUTC();

		$description = $this->parameters['event']->getDescription();
		if (strlen($description) > 1000) {
			$description = substr($description, 0, 1000)." ....";
		}
		$description .= "\n\nFor More: ".$eventURL;
		if ($this->parameters['event']->getUrl()) {
			$description .= " or " . $this->parameters['event']->getUrl();
		}
		if ($this->parameters['event']->getTicketUrl()) {
			$description .= "\n\nFor Tickets: ".$this->parameters['event']->getTicketUrl();
		}

		$newURL = "https://www.google.com/calendar/render?".
			"action=TEMPLATE&text=".urlencode($this->parameters['event']->getSummaryDisplay())."&".
			"dates=".$start->format("Ymd")."T".$start->format("His")."Z/".$end->format("Ymd")."T".$end->format("His")."Z&".
			"details=".urlencode($description)."&".
			"location=".urlencode(substr(implode(", ", $locationStringBits),0,300))."&".
			"pli=1&sf=true&output=xml#f";

		return $app->redirect($newURL);

	}
	
	
}


