<?php

namespace site\controllers;

use Silex\Application;
use site\forms\VenueEditForm;
use Symfony\Component\HttpFoundation\Request;
use models\VenueModel;
use models\AreaModel;
use repositories\VenueRepository;
use repositories\CountryRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;

use repositories\builders\filterparams\EventFilterParams;
use site\forms\UploadNewMediaForm;
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
		$this->parameters = array('country'=>null,'area'=>null, 'parentAreas'=>array(), 'childAreas'=>array());
		
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
				
				if (isset($_POST['areas']) && is_array($_POST['areas'])) {
					$areaRepository = new AreaRepository();
					$countryRepository = new CountryRepository();
					$area = null;
					foreach ($_POST['areas'] as $areaCode) {
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
					if ($area) {
						$this->parameters['venue']->setAreaId($area->getId());
					}
				}
				
				$venueRepository = new VenueRepository();
				$venueRepository->edit($this->parameters['venue'], userGetCurrent());
				
				return $app->redirect("/venue/".$this->parameters['venue']->getSlug());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/venue/edit.html.twig', $this->parameters);
		
	}	
	
	function calendarNow($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "GrouVenuep does not exist.");
		}

		
		$now = \TimeSource::getDateTime();
		return $app->redirect("/venue/".$this->parameters['venue']->getSlug()."/calendar/".$now->format("Y")."/".$now->format("m"));
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
		global $CONFIG, $FLASHMESSAGES;
		
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
							$form['title']->getData(),$form['source_text']->getData(),$form['sorce_url']->getData());
					
					if ($media) {

						$mediaInVenueRepo = new MediaInVenueRepository();
						$mediaInVenueRepo->add($media, $this->parameters['venue'], userGetCurrent());
						
						$FLASHMESSAGES->addMessage('Picuture added!');
						return $app->redirect("/venue/".$this->parameters['venue']->getSlug());
						
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
		global $CONFIG, $FLASHMESSAGES, $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		if (isset($_POST) && isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $mediaslug);
			if ($media) {
				$mediaInVenueRepo = new MediaInVenueRepository();
				$mediaInVenueRepo->remove($media, $this->parameters['venue'], userGetCurrent());
				$FLASHMESSAGES->addMessage('Removed!');
			}
		}
		
		return $app->redirect("/venue/".$this->parameters['venue']->getSlug().'/media');
	}
	
	function mediaAddExisting($slug, Request $request, Application $app) {
		global $CONFIG, $FLASHMESSAGES, $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
			
		if (isset($_POST) && isset($_POST['addMedia']) && isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $_POST['addMedia']);
			if ($media) {
				$mediaInVenueRepo = new MediaInVenueRepository();
				$mediaInVenueRepo->add($media, $this->parameters['venue'], userGetCurrent());
				$FLASHMESSAGES->addMessage('Added!');
				return $app->redirect("/venue/".$this->parameters['venue']->getSlug().'/');
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
		global $CONFIG, $FLASHMESSAGES, $WEBSESSION;
	
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Venue does not exist.");
		}
		
		if (isset($_POST) && isset($_POST['area']) && isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			
			if ($_POST['area'] == 'new' && trim($_POST['newAreaTitle']) && $this->parameters['country']) {
				
				$area = new AreaModel();
				$area->setTitle(trim($_POST['newAreaTitle']));
				
				$areaRepository = new AreaRepository();
				$areaRepository->create($area, $this->parameters['area'], $app['currentSite'], $this->parameters['country'], userGetCurrent());
				
				$this->parameters['venue']->setAreaId($area->getId());
				$venueRepository = new VenueRepository();
				$venueRepository->edit($this->parameters['venue'], userGetCurrent());
				
				$areaRepository->buildCacheAreaHasParent($area);
				
				$FLASHMESSAGES->addMessage('Thank you; venue updated!');
				
			} elseif (intval($_POST['area'])) {
				
				$areaRepository = new AreaRepository();
				$area = $areaRepository->loadBySlug($app['currentSite'], $_POST['area']);
				if ($area) {

					$this->parameters['venue']->setAreaId($area->getId());
					$venueRepository = new VenueRepository();
					$venueRepository->edit($this->parameters['venue'], userGetCurrent());
					$FLASHMESSAGES->addMessage('Thank you; venue updated!');
				}
			
			}
			
			
		}
		
		
		return $app->redirect("/venue/".$this->parameters['venue']->getSlug().'/');

	}
	
}


