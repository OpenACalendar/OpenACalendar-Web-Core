<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\CuratedListModel;
use repositories\CuratedListRepository;
use repositories\builders\filterparams\EventFilterParams;
use site\forms\CuratedListEditForm;
use repositories\UserAccountRepository;
use repositories\builders\UserAccountRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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
		
		
		$this->parameters['currentUserCanEditCuratedList'] = $this->parameters['curatedlist']->canUserEdit(userGetCurrent());
		
		return true;

	}

	function show($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}
		
		$this->parameters['eventListFilterParams'] = new EventFilterParams();
		$this->parameters['eventListFilterParams']->set($_GET);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		
		
		$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();
		
		
		
		return $app['twig']->render('site/curatedlist/show.html.twig',$this->parameters);
	}
	

	function curators($slug,Request $request, Application $app) {
		global $FLASHMESSAGES, $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}
				
		$userAccountRepository = new UserAccountRepository();
		$this->parameters['curatedlistOwner'] = $userAccountRepository->loadByOwnerOfCuratedList($this->parameters['curatedlist']);
		
		if (isset($_POST['submitted']) && $_POST['submitted'] == 'add' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			$newUser = $userAccountRepository->loadByUserName($_POST['userdetails']);
			if ($newUser){
				$curatedListRepo = new CuratedListRepository();
				$curatedListRepo->addEditorToCuratedList($newUser, $this->parameters['curatedlist'], userGetCurrent());
				$FLASHMESSAGES->addMessage("Added");
			} else {
				$FLASHMESSAGES->addError("Could not find that user");
				// TODO put error in form instead, in usual field error place
			}
		} else if (isset($_POST['submitted']) && $_POST['submitted'] == 'remove' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			$oldUser = $userAccountRepository->loadByUserName($_POST['username']);
			if ($oldUser) {
				$curatedListRepo = new CuratedListRepository();
				$curatedListRepo->removeEditorFromCuratedList($oldUser, $this->parameters['curatedlist'], userGetCurrent());
				$FLASHMESSAGES->addMessage("Removed");
			}
		}


		$userRepoBuilder = new UserAccountRepositoryBuilder();
		$userRepoBuilder->canEditNotOwnCuratedList($this->parameters['curatedlist']);
		$this->parameters['curatedlistEditors'] = $userRepoBuilder->fetchAll();
		
		return $app['twig']->render('site/curatedlist/curators.html.twig',$this->parameters);
	}
	
	
	function edit($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}
		
		if (!$this->parameters['currentUserCanEditCuratedList']) {
			$app->abort(404, "curatedlist does not exist for editing.");
		}
		
		$form = $app['form.factory']->create(new CuratedListEditForm(), $this->parameters['curatedlist']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$clistRepository = new CuratedListRepository();
				$clistRepository->edit($this->parameters['curatedlist'], userGetCurrent());
				
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

		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
			$this->parameters['showCurrentUserOptions'] = true;
		}
		$this->parameters['calendar']->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['curatedlist']->getTitle();
		return $app['twig']->render('/site/calendarPage.html.twig', $this->parameters);
	}
	
	function calendar($slug, $year, $month, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		
		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
			$this->parameters['showCurrentUserOptions'] = true;
		}
		$this->parameters['calendar']->byMonth($year, $month, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['curatedlist']->getTitle();
		return $app['twig']->render('/site/calendarPage.html.twig', $this->parameters);
	}
	
	
}

