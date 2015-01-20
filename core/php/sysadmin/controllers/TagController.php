<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\TagRepository;
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
class TagController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$tr = new TagRepository();
		$this->parameters['tag'] = $tr->loadBySlug($this->parameters['site'], $slug);
		
		if (!$this->parameters['tag']) {
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
			
				if ($action->getCommand() == 'delete' && !$this->parameters['tag']->getIsDeleted()) {
					$tr = new TagRepository();
					$tr->delete($this->parameters['tag'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/tag/'.$this->parameters['tag']->getSlug());
				} else if ($action->getCommand() == 'undelete' && $this->parameters['tag']->getIsDeleted()) {
					$this->parameters['tag']->setIsDeleted(false);
					$tr = new TagRepository();
					$tr->undelete($this->parameters['tag'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/tag/'.$this->parameters['tag']->getSlug());
				}
			}
		}
		
		$this->parameters['form'] = $form->createView();
			
		return $app['twig']->render('sysadmin/tag/index.html.twig', $this->parameters);		
	
	}
	
	
}


