<?php

namespace site\controllers;

use Silex\Application;
use site\forms\VenueEditForm;
use Symfony\Component\HttpFoundation\Request;
use models\VenueModel;
use models\AreaModel;
use repositories\VenueRepository;
use repositories\EventRepository;
use repositories\CountryRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;

use repositories\builders\filterparams\EventFilterParams;
use site\forms\UploadNewMediaForm;
use site\forms\VenueDeleteForm;
use repositories\MediaRepository;
use repositories\AreaRepository;
use repositories\MediaInVenueRepository;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\AreaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		global $CONFIG;
		$this->parameters = array('country'=>null,'area'=>null, 'parentAreas'=>array(), 'childAreas'=>array());
		
		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$vr = new VenueRepository();
		$this->parameters['venue'] = $vr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['venue']) {
			return false;
		}
		
		if ($this->parameters['venue']->getCountryID()) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadById($this->parameters['venue']->getCountryID());
		}
		
		if ($this->parameters['venue']->getAreaId()) {	
			$ar = new AreaRepository();
			$this->parameters['area'] = $ar->loadById($this->parameters['venue']->getAreaId());
			if (!$this->parameters['area']) {
				return false;
			}

			$checkArea = $this->parameters['area']->getParentAreaId() ? $ar->loadById($this->parameters['area']->getParentAreaId())  : null;
			while($checkArea) {
				array_unshift($this->parameters['parentAreas'],$checkArea);
				$checkArea = $checkArea->getParentAreaId() ? $ar->loadById($checkArea->getParentAreaId())  : null;
			}

			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setSite($app['currentSite']);
			$areaRepoBuilder->setCountry($this->parameters['country']);
			$areaRepoBuilder->setParentArea($this->parameters['area']);
			$areaRepoBuilder->setIncludeDeleted(false);
			$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();
		} else {
			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setSite($app['currentSite']);
			$areaRepoBuilder->setCountry($this->parameters['country']);
			$areaRepoBuilder->setNoParentArea(true);
			$areaRepoBuilder->setIncludeDeleted(false);
			$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();
		}


		$app['currentUserActions']->set("org.openacalendar","venueHistory",true);
		$app['currentUserActions']->set("org.openacalendar","venueEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeaturePhysicalEvents()
			&& !$this->parameters['venue']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","venueDelete",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeaturePhysicalEvents()
			&& !$this->parameters['venue']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","venueEditMedia",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeaturePhysicalEvents()
			&& !$this->parameters['venue']->getIsDeleted()
			&& $CONFIG->isFileStore());

		$app['currentUserActions']->set("org.openacalendar","venueEditPushToChildAreas",
			$this->parameters['childAreas'] &&
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeaturePhysicalEvents()
			&& !$this->parameters['venue']->getIsDeleted());


		return true;
	}
	
	function show($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		$this->parameters['eventListFilterParams'] = new EventFilterParams();
		$this->parameters['eventListFilterParams']->set($_GET);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setVenue($this->parameters['venue']);
		if (userGetCurrent()) {
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
		}
		
		$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setVenue($this->parameters['venue']);
		$this->parameters['medias'] = $mrb->fetchAll();
				
		return $app['twig']->render('site/venue/show.html.twig', $this->parameters);
	}
	
	
	
	function edit($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}

		
		if ($this->parameters['venue']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		$form = $app['form.factory']->create(new VenueEditForm($app['currentSite']), $this->parameters['venue']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$area = null;
				
				if (is_array($request->request->get('areas'))) {
					$areaRepository = new AreaRepository();
					$countryRepository = new CountryRepository();
					foreach ($request->request->get('areas') as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						} else if (substr($areaCode, 0, 4) == 'NEW:') {
							$newArea = new AreaModel();
							$newArea->setTitle(substr($areaCode, 4));
							$areaRepository->create($newArea, $area, $app['currentSite'], $countryRepository->loadById($venue->getCountryId()) , userGetCurrent());
							$areaRepository->buildCacheAreaHasParent($newArea);
							$area = $newArea;
						}
					}
				}
				
				if ($area) {
					$this->parameters['venue']->setAreaId($area->getId());
				} else {
					$this->parameters['venue']->setAreaId(null);
				}
				
				$venueRepository = new VenueRepository();
				$venueRepository->edit($this->parameters['venue'], userGetCurrent());
				
				return $app->redirect("/venue/".$this->parameters['venue']->getSlugForURL());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/venue/edit.html.twig', $this->parameters);
		
	}	
	
	function calendarNow($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "GrouVenuep does not exist.");
		}

		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setVenue($this->parameters['venue']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
			$this->parameters['showCurrentUserOptions'] = true;
		}	
		$this->parameters['calendar']->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['venue']->getTitle();
		return $app['twig']->render('/site/calendarPage.html.twig', $this->parameters);
	}
	
	function calendar($slug, $year, $month, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}

		
		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setVenue($this->parameters['venue']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
			$this->parameters['showCurrentUserOptions'] = true;
		}	
		$this->parameters['calendar']->byMonth($year, $month, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['venue']->getTitle();
		return $app['twig']->render('/site/calendarPage.html.twig', $this->parameters);
	}
	
	function history($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		
		
		$historyRepositoryBuilder = new HistoryRepositoryBuilder();
		$historyRepositoryBuilder->setVenue($this->parameters['venue']);		
		$this->parameters['historyItems'] = $historyRepositoryBuilder->fetchAll();
		
		return $app['twig']->render('site/venue/history.html.twig', $this->parameters);
	}
	
	
	function media($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		if ($app['currentUserCanEditSite'] && $app['currentSite']->getIsFeaturePhysicalEvents()) {
			
			
			$form = $app['form.factory']->create(new UploadNewMediaForm());
		
			
			if ('POST' == $request->getMethod()) {
				$form->bind($request);

				if ($form->isValid()) {

					$mediaRepository = new MediaRepository();
					$media = $mediaRepository->createFromFile($form['media']->getData(), $app['currentSite'], userGetCurrent(),
							$form['title']->getData(),$form['source_text']->getData(),$form['source_url']->getData());
					
					if ($media) {

						$mediaInVenueRepo = new MediaInVenueRepository();
						$mediaInVenueRepo->add($media, $this->parameters['venue'], userGetCurrent());
						
						$app['flashmessages']->addMessage('Picture added!');
						return $app->redirect("/venue/".$this->parameters['venue']->getSlugForURL());
						
					}
					
				}
			}
			$this->parameters['uploadNewMediaForm'] = $form->createView();
			
		}
		
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setVenue($this->parameters['venue']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/venue/media.html.twig', $this->parameters);
	}
	
	function mediaRemove($slug, $mediaslug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $mediaslug);
			if ($media) {
				$mediaInVenueRepo = new MediaInVenueRepository();
				$mediaInVenueRepo->remove($media, $this->parameters['venue'], userGetCurrent());
				$app['flashmessages']->addMessage('Removed!');
			}
		}
		
		return $app->redirect("/venue/".$this->parameters['venue']->getSlugForURL().'/media');
	}
	
	function mediaAddExisting($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
			
		if ($request->request->get('addMedia') && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $request->request->get('addMedia'));
			if ($media) {
				$mediaInVenueRepo = new MediaInVenueRepository();
				$mediaInVenueRepo->add($media, $this->parameters['venue'], userGetCurrent());
				$app['flashmessages']->addMessage('Added!');
				return $app->redirect("/venue/".$this->parameters['venue']->getSlugForURL().'/');
			}
		}
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setNotInVenue($this->parameters['venue']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/venue/media.add.existing.html.twig', $this->parameters);
	}
	
	function moveToArea($slug, Request $request, Application $app) {	
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		if ($request->request->get('area') && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
			if (intval($request->request->get('area'))) {
				
				$areaRepository = new AreaRepository();
				$area = $areaRepository->loadBySlug($app['currentSite'], $request->request->get('area'));
				if ($area && (!$this->parameters['area'] || $area->getId() != $this->parameters['area']->getId())) {

					$this->parameters['venue']->setAreaId($area->getId());
					$venueRepository = new VenueRepository();
					$venueRepository->edit($this->parameters['venue'], userGetCurrent());
					$app['flashmessages']->addMessage('Thank you; venue updated!');
				}
			
			}
			
			
		}
		
		
		return $app->redirect("/venue/".$this->parameters['venue']->getSlugForURL().'/');

	}
	
	
	
	function delete($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}

		if ($this->parameters['venue']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		$form = $app['form.factory']->create(new VenueDeleteForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->moveAllFutureEventsAtVenueToNoSetVenue($this->parameters['venue'], userGetCurrent());

				$venueRepository = new VenueRepository();
				$venueRepository->delete($this->parameters['venue'], userGetCurrent());
				
				return $app->redirect("/venue/".$this->parameters['venue']->getSlugForURL());
				
			}
		}
		
		
		$rb = new EventRepositoryBuilder();
		$rb->setVenue($this->parameters['venue']);
		$rb->setAfterNow(true);
		$rb->setIncludeDeleted(false);
		$this->parameters['events'] = $rb->fetchAll();
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/venue/delete.html.twig', $this->parameters);
		
	}
	
}


