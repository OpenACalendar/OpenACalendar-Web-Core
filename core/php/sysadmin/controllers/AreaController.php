<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\CountryRepository;
use repositories\SiteRepository;
use repositories\AreaRepository;
use repositories\builders\SiteRepositoryBuilder;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('area'=>null,'parentarea'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$ar = new AreaRepository();
		$this->parameters['area'] = $ar->loadBySlug($this->parameters['site'], $slug);

		$this->parameters['areaisduplicateof'] = $this->parameters['area']->getIsDuplicateOfId() ? $ar->loadById($this->parameters['area']->getIsDuplicateOfId()) : null;

		if (!$this->parameters['area']) {
			$app->abort(404);
		}
		
		if ($this->parameters['area']->getParentAreaId()) {
			$this->parameters['parentarea'] = $ar->loadById($this->parameters['area']->getParentAreaId());
		}
		
		
		$cr = new CountryRepository();
		$this->parameters['country'] = $this->parameters['area']->getCountryId() 
				? $cr->loadById($this->parameters['area']->getCountryId()) : null; 
		
	
	}
	
	function index($siteid, $slug, Request $request, Application $app) {
		global $CONFIG;

		$this->build($siteid, $slug, $request, $app);
		
				
				
		$form = $app['form.factory']->create(new ActionForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
			
				if ($action->getCommand() == 'delete' && !$this->parameters['area']->getIsDeleted()) {
					$ar = new AreaRepository();
					$ar->delete($this->parameters['area'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/'.$this->parameters['area']->getSlug());
				} else if ($action->getCommand() == 'undelete' && $this->parameters['area']->getIsDeleted()) {
					$this->parameters['area']->setIsDeleted(false);
					$ar = new AreaRepository();
					$ar->edit($this->parameters['area'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/'.$this->parameters['area']->getSlug());
				} else if ($action->getCommand() == 'parentarea') {
					$ar = new AreaRepository();
					$newparentarea = $ar->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($newparentarea) {
						// TODO make sure they aren't doing something dumb like moving under themselves or making a loop
						$this->parameters['area']->setParentAreaId($newparentarea->getId());
						$ar->editParentArea($this->parameters['area'], $app['currentUser']);
					}
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/'.$this->parameters['area']->getSlug());

				} else if ($action->getCommand() == 'isduplicateof') {

					$ar = new AreaRepository();
					$originalArea = $ar->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($originalArea && $originalArea->getId() != $this->parameters['area']->getId()) {
						$ar->markDuplicate($this->parameters['area'], $originalArea, $app['currentUser']);
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/'.$this->parameters['area']->getSlug());
					}


				} else if ($action->getCommand() == 'purge' && $CONFIG->sysAdminExtraPurgeAreaPassword && $CONFIG->sysAdminExtraPurgeAreaPassword == $action->getParam(0)) {

					$ar = new AreaRepository();
					$ar->purge($this->parameters['area']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/area/');


				}
			}
		}
		
		$this->parameters['form'] = $form->createView();
			
		
		
		return $app['twig']->render('sysadmin/area/index.html.twig', $this->parameters);		
	
	}
	
	
	
	
}


