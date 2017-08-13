<?php

namespace sysadmin\controllers;

use repositories\builders\UserAccountRepositoryBuilder;
use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\CountryRepository;
use repositories\SiteRepository;
use repositories\AreaRepository;
use repositories\builders\SiteRepositoryBuilder;
use sysadmin\forms\ActionWithCommentForm;
use sysadmin\ActionParser;
use repositories\builders\SysadminCommentRepositoryBuilder;
use repositories\SysAdminCommentRepository;

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
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('area'=>null,'parentarea'=>null);

		$sr = new SiteRepository($app);
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$ar = new AreaRepository($app);
		$this->parameters['area'] = $ar->loadBySlug($this->parameters['site'], $slug);

		if (!$this->parameters['area']) {
			$app->abort(404);
		}

		$this->parameters['areaisduplicateof'] = $this->parameters['area']->getIsDuplicateOfId() ? $ar->loadById($this->parameters['area']->getIsDuplicateOfId()) : null;

		if ($this->parameters['area']->getParentAreaId()) {
			$this->parameters['parentarea'] = $ar->loadById($this->parameters['area']->getParentAreaId());
		}
		
		
		$cr = new CountryRepository($app);
		$this->parameters['country'] = $this->parameters['area']->getCountryId() 
				? $cr->loadById($this->parameters['area']->getCountryId()) : null; 
		
	
	}
	
	function index($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
		
				
				
		$form = $app['form.factory']->create(ActionWithCommentForm::class);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);


				$redirect = false;

				if ($data['comment']) {
					$scr = new SysAdminCommentRepository($app);
					$scr->createAboutArea($this->parameters['area'], $data['comment'], $app['currentUser']);
					$redirect = true;
				}


				if ($action->getCommand() == 'delete' && !$this->parameters['area']->getIsDeleted()) {
					$ar = new AreaRepository($app);
					$ar->delete($this->parameters['area'],  $app['currentUser']);
					$redirect = true;
				} else if ($action->getCommand() == 'undelete' && $this->parameters['area']->getIsDeleted()) {
					$this->parameters['area']->setIsDeleted(false);
					$ar = new AreaRepository($app);
					$ar->undelete($this->parameters['area'],  $app['currentUser']);
					$redirect = true;
				} else if ($action->getCommand() == 'parentarea') {
					$ar = new AreaRepository($app);
					$newparentarea = $ar->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($newparentarea) {
						// TODO make sure they aren't doing something dumb like moving under themselves or making a loop
						$this->parameters['area']->setParentAreaId($newparentarea->getId());
						$ar->editParentArea($this->parameters['area'], $app['currentUser']);
					}
					$redirect = true;

				} else if ($action->getCommand() == 'isduplicateof') {

					$ar = new AreaRepository($app);
					$originalArea = $ar->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($originalArea && $originalArea->getId() != $this->parameters['area']->getId()) {
						$ar->markDuplicate($this->parameters['area'], $originalArea, $app['currentUser']);
						$redirect = true;
					}


				} else if ($action->getCommand() == 'purge' && $app['config']->sysAdminExtraPurgeAreaPassword && $app['config']->sysAdminExtraPurgeAreaPassword == $action->getParam(0)) {

					$ar = new AreaRepository($app);
					$ar->purge($this->parameters['area']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/');


				}

				if ($redirect) {

					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/'.$this->parameters['area']->getSlug());
				}
			}
		}
		
		$this->parameters['form'] = $form->createView();


		$sacrb = new SysadminCommentRepositoryBuilder($app);
		$sacrb->setArea($this->parameters['area']);
		$this->parameters['comments'] = $sacrb->fetchAll();
		
		return $app['twig']->render('sysadmin/area/index.html.twig', $this->parameters);		
	
	}




	function watchers($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);


		$uarb = new UserAccountRepositoryBuilder($app);
		$uarb->setWatchesArea($this->parameters['area']);
		$this->parameters['watchers'] = $uarb->fetchAll();


		return $app['twig']->render('sysadmin/area/watchers.html.twig', $this->parameters);

	}



}


