<?php

namespace org\openacalendar\curatedlists\site\controllers;


use repositories\builders\EventRepositoryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use org\openacalendar\curatedlists\models\CuratedListModel;
use org\openacalendar\curatedlists\repositories\CuratedListRepository;
use repositories\builders\filterparams\EventFilterParams;
use org\openacalendar\curatedlists\site\forms\CuratedListEditForm;
use repositories\builders\UserAccountRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListController {

	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();

		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$curatedlistRepository = new CuratedListRepository();
		$this->parameters['curatedlist'] =  $curatedlistRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['curatedlist']) {
			return false;
		}

		$this->parameters['actionCuratedListEditDetails'] = 
			$app['currentUserPermissions']->hasPermission("org.openacalendar.curatedlists","CURATED_LISTS_CHANGE") &&
            $app['currentSiteFeatures']->has('org.openacalendar.curatedlists','CuratedList') &&
			!$this->parameters['curatedlist']->getIsDeleted() &&
			$this->parameters['curatedlist']->canUserEdit($app['currentUser']);
		$this->parameters['actionCuratedListEditCurators'] = $this->parameters['actionCuratedListEditDetails'];
		$this->parameters['actionCuratedListEditContents'] = $this->parameters['actionCuratedListEditDetails'];

		return true;

	}

	function show($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		$this->parameters['eventListFilterParams'] = new EventFilterParams($app, null, $app['currentSite']);
		$this->parameters['eventListFilterParams']->set($_GET);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist'], true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeMediasSlugs(true);
		$this->parameters['eventListFilterParams']->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        $this->parameters['eventListFilterParams']->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
		if ($app['currentUser']) {
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
		
		$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();

		if (count($this->parameters['events']) == 0) {
			$erb = new EventRepositoryBuilder($app);
			$erb->setCuratedList($this->parameters['curatedlist'], false);
			$erb->setAfterNow();
			$this->parameters['eventsDefaultCount'] = $erb->fetchCount();

			$erb = new EventRepositoryBuilder($app);
			$erb->setCuratedList($this->parameters['curatedlist'], false);
			$erb->setBeforeNow();
			$this->parameters['eventsPastCount'] = $erb->fetchCount();
		}
		
		return $app['twig']->render('site/curatedlist/show.html.twig',$this->parameters);
	}
	

	function curators($slug,Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}
				
		$userAccountRepository = new \org\openacalendar\curatedlists\repositories\UserAccountRepository($app);
		$this->parameters['curatedlistOwner'] = $userAccountRepository->loadByOwnerOfCuratedList($this->parameters['curatedlist']);

		if ($this->parameters['actionCuratedListEditCurators'] && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			if ($request->request->get('submitted') == 'add') {
				$newUser = $userAccountRepository->loadByUserName($request->request->get('userdetails'));
				if ($newUser){
					$curatedListRepo = new CuratedListRepository();
					$curatedListRepo->addEditorToCuratedList($newUser, $this->parameters['curatedlist'], $app['currentUser']);
					$app['flashmessages']->addMessage("Added");
				} else {
					$app['flashmessages']->addError("Could not find that user");
					// TODO put error in form instead, in usual field error place
				}
			} else if ($request->request->get('submitted') == 'remove') {
				$oldUser = $userAccountRepository->loadByUserName($request->request->get('username'));
				if ($oldUser) {
					$curatedListRepo = new CuratedListRepository();
					$curatedListRepo->removeEditorFromCuratedList($oldUser, $this->parameters['curatedlist'], $app['currentUser']);
					$app['flashmessages']->addMessage("Removed");
				}
			}
		}


		$userRepoBuilder = new UserAccountRepositoryBuilder($app);
		$userRepoBuilder->canEditNotOwnCuratedList($this->parameters['curatedlist']);
		$this->parameters['curatedlistEditors'] = $userRepoBuilder->fetchAll();
		
		return $app['twig']->render('site/curatedlist/curators.html.twig',$this->parameters);
	}

    function groups($slug,Request $request, Application $app) {
        if (!$this->build($slug, $request, $app)) {
            $app->abort(404, "curatedlist does not exist.");
        }


        $groupBuilder = new \org\openacalendar\curatedlists\repositories\builders\GroupRepositoryBuilder($app);
        $groupBuilder->setCuratedList($this->parameters['curatedlist']);
        $this->parameters['groups'] = $groupBuilder->fetchAll();

        return $app['twig']->render('site/curatedlist/groups.html.twig',$this->parameters);
    }

	
	function editDetails($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}
		
		if (!$this->parameters['actionCuratedListEditDetails']) {
			$app->abort(404, "curatedlist does not exist for editing.");
		}
		
		$form = $app['form.factory']->create(new CuratedListEditForm(), $this->parameters['curatedlist']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$clistRepository = new CuratedListRepository();
				$clistRepository->edit($this->parameters['curatedlist'], $app['currentUser']);
				
				return $app->redirect("/curatedlist/".$this->parameters['curatedlist']->getSlug());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/curatedlist/edit.html.twig',$this->parameters);
	}
	
	
	
	function calendarNow($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

        $this->parameters['eventListFilterParams'] = new EventFilterParams($app, null, $app['currentSite']);
        $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
        $this->parameters['eventListFilterParams']->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        $this->parameters['eventListFilterParams']->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
        $this->parameters['eventListFilterParams']->setFallBackFrom(true);
        $this->parameters['eventListFilterParams']->set($_GET);

        $this->parameters['calendar'] = new \RenderCalendar($app, $this->parameters['eventListFilterParams']);

		if ($app['currentUser']) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
			$this->parameters['showCurrentUserOptions'] = true;
		}
		$this->parameters['calendar']->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['curatedlist']->getTitle();
		return $app['twig']->render('/site/curatedlist/calendar.monthly.html.twig', $this->parameters);
	}
	
	function calendar($slug, $year, $month, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

        $this->parameters['eventListFilterParams'] = new EventFilterParams($app, null, $app['currentSite']);
        $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
        $this->parameters['eventListFilterParams']->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        $this->parameters['eventListFilterParams']->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
        $this->parameters['eventListFilterParams']->setFallBackFrom(true);
        $this->parameters['eventListFilterParams']->set($_GET);

        $this->parameters['calendar'] = new \RenderCalendar($app, $this->parameters['eventListFilterParams']);

		if ($app['currentUser']) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
			$this->parameters['showCurrentUserOptions'] = true;
		}
		$this->parameters['calendar']->byMonth($year, $month, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['curatedlist']->getTitle();
		return $app['twig']->render('/site/curatedlist/calendar.monthly.html.twig', $this->parameters);
	}
	
	
}

