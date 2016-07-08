<?php

namespace site\controllers;

use models\AreaEditMetaDataModel;
use repositories\UserAccountRepository;
use repositories\UserWatchesAreaRepository;
use repositories\UserWatchesAreaStopRepository;
use Silex\Application;
use site\forms\AreaNewInAreaForm;
use Symfony\Component\HttpFoundation\Request;
use models\AreaModel;
use models\VenueModel;
use repositories\AreaRepository;
use repositories\CountryRepository;
use repositories\VenueRepository;
use repositories\builders\VenueRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;

use repositories\builders\filterparams\EventFilterParams;
use site\forms\UploadNewMediaForm;
use site\forms\AreaEditForm;
use site\forms\AreaNewVenueInAreaForm;
use repositories\MediaRepository;
use repositories\MediaInVenueRepository;
use repositories\builders\AreaRepositoryBuilder;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array(
			'country'=>null,
			'parentAreas'=>array(),
			'areaIsDuplicateOf'=>null,
		);
		
		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$ar = new AreaRepository($app);
		$this->parameters['area'] = $ar->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['area']) {
			return false;
		}
		
		$checkArea = $this->parameters['area']->getParentAreaId() ? $ar->loadById($this->parameters['area']->getParentAreaId())  : null;
		while($checkArea) {
			array_unshift($this->parameters['parentAreas'],$checkArea);
			$checkArea = $checkArea->getParentAreaId() ? $ar->loadById($checkArea->getParentAreaId())  : null;
		}
		
		
		if ($app['currentUser']) {
			$uwgr = new UserWatchesAreaRepository($app);
			$uwg = $uwgr->loadByUserAndArea($app['currentUser'], $this->parameters['area']);
			$this->parameters['currentUserWatchesArea'] = $uwg && $uwg->getIsWatching();
		}

		
		$cr = new CountryRepository($app);
		$this->parameters['country'] = $cr->loadById($this->parameters['area']->getCountryID());
						
		$areaRepoBuilder = new AreaRepositoryBuilder($app);
		$areaRepoBuilder->setSite($app['currentSite']);
		$areaRepoBuilder->setCountry($this->parameters['country']);
		$areaRepoBuilder->setParentArea($this->parameters['area']);
		$areaRepoBuilder->setIncludeDeleted(false);
		$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();

		if ($this->parameters['area']->getIsDuplicateOfId()) {
			$this->parameters['areaIsDuplicateOf'] = $ar->loadByID($this->parameters['area']->getIsDuplicateOfId());
		}
		
		$app['currentUserActions']->set("org.openacalendar","areaHistory",true);
		$app['currentUserActions']->set("org.openacalendar","actionAreaEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","AREAS_CHANGE")
			&& !$this->parameters['area']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","actionAreaNew",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","AREAS_CHANGE")
			&& !$this->parameters['area']->getIsDeleted());

		return true;
	}
	
	function show($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}
		
		$this->parameters['eventListFilterParams'] = new EventFilterParams($app, null, $app['currentSite']);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setArea($this->parameters['area']);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeMediasSlugs(true);
        $this->parameters['eventListFilterParams']->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        $this->parameters['eventListFilterParams']->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));;
		if ($app['currentUser']) {
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
        $this->parameters['eventListFilterParams']->set($_GET);

		$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();


				
		return $app['twig']->render('site/area/show.html.twig', $this->parameters);
	}
	
	
	
	function newSplash($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}	
		
		
		return $app['twig']->render('site/area/new.html.twig', $this->parameters);
		
	}
	
	function newArea($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}	
		
		$area = new AreaModel();
		
		$form = $app['form.factory']->create(new AreaNewInAreaForm(), $area);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$areaRepository = new AreaRepository($app);
				$areaRepository->create($area, $this->parameters['area'], $app['currentSite'], $this->parameters['country'], $app['currentUser']);
				$areaRepository->buildCacheAreaHasParent($area);
				return $app->redirect("/area/".$area->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/area/newarea.html.twig', $this->parameters);
		
	}
	
	function editDetails($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		
		if ($this->parameters['area']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		$form = $app['form.factory']->create(new AreaEditForm($app), $this->parameters['area']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {


				$areaEditMetaDataModel = new AreaEditMetaDataModel();
				$areaEditMetaDataModel->setUserAccount($app['currentUser']);
				if ($form->has('edit_comment')) {
					$areaEditMetaDataModel->setEditComment($form->get('edit_comment')->getData());
				}
				$areaEditMetaDataModel->setFromRequest($request);

				$areaRepository = new AreaRepository($app);
				$areaRepository->editWithMetaData($this->parameters['area'], $areaEditMetaDataModel);
				
				return $app->redirect("/area/".$this->parameters['area']->getSlugForURL());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/area/edit.details.html.twig', $this->parameters);
		
	}	
	
	
	
	
	function calendarNow($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

        $this->parameters['eventListFilterParams'] = new EventFilterParams($app, null, $app['currentSite']);
        $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setArea($this->parameters['area']);
        $this->parameters['eventListFilterParams']->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        $this->parameters['eventListFilterParams']->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
        $this->parameters['eventListFilterParams']->setFallBackFrom(true);
        $this->parameters['eventListFilterParams']->set($_GET);

		$this->parameters['calendar'] = new \RenderCalendar($app, $this->parameters['eventListFilterParams']);

        if ($app['currentUser']) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
		$this->parameters['calendar']->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['area']->getTitle();
		return $app['twig']->render('/site/area/calendar.monthly.html.twig', $this->parameters);
	}
	
	function calendar($slug, $year, $month, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "area does not exist.");
		}

        $this->parameters['eventListFilterParams'] = new EventFilterParams($app, null, $app['currentSite']);
        $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setArea($this->parameters['area']);
        $this->parameters['eventListFilterParams']->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        $this->parameters['eventListFilterParams']->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
        $this->parameters['eventListFilterParams']->setFallBackFrom(true);
        $this->parameters['eventListFilterParams']->set($_GET);

        $this->parameters['calendar'] = new \RenderCalendar($app, $this->parameters['eventListFilterParams']);

        if ($app['currentUser']) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
		$this->parameters['calendar']->byMonth($year, $month, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['area']->getTitle();
		return $app['twig']->render('/site/area/calendar.monthly.html.twig', $this->parameters);
	}
	
	
	function infoJson($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}	
		
		$data = array(
				'area'=>array(
					'slug'=>$this->parameters['area']->getSlug(),
					'title'=>$this->parameters['area']->getTitle(),
					'max_lat'=>$this->parameters['area']->getCachedMaxLat(),
					'max_lng'=>$this->parameters['area']->getCachedMaxLng(),
					'min_lat'=>$this->parameters['area']->getCachedMinLat(),
					'min_lng'=>$this->parameters['area']->getCachedMinLng(),
				),
				'childAreas'=>array(),
				'venues'=>array(),
			);
		
		foreach($this->parameters['childAreas'] as $childArea) {
			$data['childAreas'][] = array(
				'slug'=>$childArea->getSlug(),
				'title'=>$childArea->getTitle(),
				'max_lat'=>$childArea->getCachedMaxLat(),
				'max_lng'=>$childArea->getCachedMaxLng(),
				'min_lat'=>$childArea->getCachedMinLat(),
				'min_lng'=>$childArea->getCachedMinLng(),
			);
		}
		
		if (isset($_GET['includeVenues']) && $_GET['includeVenues']) {
			$vrb = new VenueRepositoryBuilder($app);
			$vrb->setIncludeDeleted(false);
			$vrb->setSite($app['currentSite']);
			$vrb->setArea($this->parameters['area']);
			foreach($vrb->fetchAll() as $venue) {
				$data['venues'][$venue->getId()] = array(
						'slug'=>$venue->getSlug(),
						'title'=>$venue->getTitle(),
						'lat'=>$venue->getLat(),
						'lng'=>$venue->getLng(),
					);
			}
		}
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		$response->setPublic();
		$response->setMaxAge($app['config']->cacheFeedsInSeconds);
		return $response;
	
	}

	function history($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}
		
		
		
		$historyRepositoryBuilder = new HistoryRepositoryBuilder($app);
		$historyRepositoryBuilder->getHistoryRepositoryBuilderConfig()->setArea($this->parameters['area']);
		$this->parameters['historyItems'] = $historyRepositoryBuilder->fetchAll();
		
		return $app['twig']->render('site/area/history.html.twig', $this->parameters);
	}


	function watch($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		if ($request->request->get('action')  && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$repo = new UserWatchesAreaRepository($app);
			if ($request->request->get('action') == 'watch') {
				$repo->startUserWatchingArea($app['currentUser'], $this->parameters['area']);
				$app['flashmessages']->addMessage("Watching!");
			} else if ($request->request->get('action') == 'unwatch') {
				$repo->stopUserWatchingArea($app['currentUser'], $this->parameters['area']);
				$app['flashmessages']->addMessage("No longer watching");
			}
			// redirect here because if we didn't the  $this->parameters vars would be wrong (the old state)
			// this is an easy way to get round that. Also it's nice UI to go back to the area page.
			return $app->redirect('/area/'.$this->parameters['area']->getSlugForURL());
		}

		$repo = new \repositories\UserNotificationPreferenceRepository($app);
		$this->parameters['preferences'] = array();
		foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
			if (!isset($this->parameters['preferences'][$extension->getId()])) {
				$this->parameters['preferences'][$extension->getId()] = array();
			}
			foreach($extension->getUserNotificationPreferenceTypes() as $type) {
				$userPref = $repo->load($app['currentUser'],$extension->getId() ,$type);
				$this->parameters['preferences'][$extension->getId()][$type] = array('email'=>$userPref->getIsEmail());
			}
		}

		return $app['twig']->render('site/area/watch.html.twig', $this->parameters);
	}

	function stopWatchingFromEmail($slug, $userid, $code,Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Area does not exist.");
		}

		$userRepo = new UserAccountRepository($app);
		$user = $userRepo->loadByID($userid);
		if (!$user) {
			$app['monolog']->addError("Failed stop watching area from email - no user ");
			die("NO"); // TODO
		}

		$userWatchesAreaStopRepo = new UserWatchesAreaStopRepository($app);
		$userWatchesAreaStop = $userWatchesAreaStopRepo->loadByUserAccountIDAndAreaIDAndAccessKey($user->getId(), $this->parameters['area']->getId(), $code);
		if (!$userWatchesAreaStop) {
			$app['monolog']->addError("Failed stop watching area from email - user ".$user->getId()." - code wrong");
			die("NO"); // TODO
		}

		$userWatchesAreaRepo = new UserWatchesAreaRepository($app);
		$userWatchesArea = $userWatchesAreaRepo->loadByUserAndArea($user, $this->parameters['area']);
		if (!$userWatchesArea || !$userWatchesArea->getIsWatching()) {
			$app['monolog']->addError("Failed stop watching area from email - user ".$user->getId()." - not watching");
			die("You don't watch this area"); // TODO
		}

		if ($request->request->get('action') == 'unwatch' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$userWatchesAreaRepo->stopUserWatchingArea($user, $this->parameters['area']);
			// redirect here because if we didn't the twig global and $app vars would be wrong (the old state)
			// this is an easy way to get round that.
			$app['flashmessages']->addMessage("You have stopped watching this area.");
			return $app->redirect('/area/'.$this->parameters['area']->getSlugForURL());
		}

		$this->parameters['user'] = $user;

		return $app['twig']->render('site/area/stopWatchingFromEmail.html.twig', $this->parameters);

	}
	
}


