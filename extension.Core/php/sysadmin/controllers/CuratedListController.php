<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\CuratedListRepository;
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
class CuratedListController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$clr = new CuratedListRepository();
		$this->parameters['curatedlist'] = $clr->loadBySlug($this->parameters['site'], $slug);
		
		if (!$this->parameters['curatedlist']) {
			$app->abort(404);
		}
	
	}
	
	function index($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
		
				
		$form = $app['form.factory']->create(new ActionForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
				
				if ($action->getCommand() == 'addeditor') {
					$userRepo = new UserAccountRepository;
					$user = $userRepo->loadByID($action->getParam(0));
					if ($user) {
						$clr = new CuratedListRepository();
						$clr->addEditorToCuratedList($user, $this->parameters['curatedlist'], userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/curatedlist/'.$this->parameters['curatedlist']->getSlug());
					}					
				} else if ($action->getCommand() == 'removeeditor') {
					$userRepo = new UserAccountRepository;
					$user = $userRepo->loadByID($action->getParam(0));
					if ($user) {
						$clr = new CuratedListRepository();
						$clr->removeEditorFromCuratedList($user, $this->parameters['curatedlist'], userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/curatedlist/'.$this->parameters['curatedlist']->getSlug());
					}					
				}
			}
		}
		
		$this->parameters['form'] = $form->createView();
			
		
		
		return $app['twig']->render('sysadmin/curatedlist/index.html.twig', $this->parameters);		
	
	}
	
	
	
	
}


