<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
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
class GroupController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$gr = new GroupRepository();
		$this->parameters['group'] = $gr->loadBySlug($this->parameters['site'], $slug);
		
		if (!$this->parameters['group']) {
			$app->abort(404);
		}

		$this->parameters['groupisduplicateof'] = $this->parameters['group']->getIsDuplicateOfId() ? $gr->loadById($this->parameters['group']->getIsDuplicateOfId()) : null;
	
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
			
				if ($action->getCommand() == 'delete' && !$this->parameters['group']->getIsDeleted()) {
					$gr = new GroupRepository();
					$gr->delete($this->parameters['group'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/'.$this->parameters['group']->getSlug());
				} else if ($action->getCommand() == 'undelete' && $this->parameters['group']->getIsDeleted()) {
					$this->parameters['group']->setIsDeleted(false);
					$gr = new GroupRepository();
					$gr->edit($this->parameters['group'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/'.$this->parameters['group']->getSlug());
				} else if ($action->getCommand() == 'isduplicateof') {
					$gr = new GroupRepository();
					$originalGroup = $gr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($originalGroup && $originalGroup->getId() != $this->parameters['group']->getId()) {
						$gr->markDuplicate($this->parameters['group'], $originalGroup, $app['currentUser']);
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/'.$this->parameters['group']->getSlug());
					}


				} else if ($action->getCommand() == 'purge' && $CONFIG->sysAdminExtraPurgeGroupPassword && $CONFIG->sysAdminExtraPurgeGroupPassword == $action->getParam(0)) {

					$gr = new GroupRepository();
					$gr->purge($this->parameters['group']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/');

				}
			}
		}
		
		$this->parameters['form'] = $form->createView();
			
		
		
		return $app['twig']->render('sysadmin/group/index.html.twig', $this->parameters);		
	
	}
	
	
	function watchers($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
		
				
		$uarb = new UserAccountRepositoryBuilder();
		$uarb->setWatchesGroup($this->parameters['group']);
		$this->parameters['watchers'] = $uarb->fetchAll();

		
		return $app['twig']->render('sysadmin/group/watchers.html.twig', $this->parameters);		
	
	}
	
	
}


