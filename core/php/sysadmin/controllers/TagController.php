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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository($app);
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$tr = new TagRepository($app);
		$this->parameters['tag'] = $tr->loadBySlug($this->parameters['site'], $slug);
		
		if (!$this->parameters['tag']) {
			$app->abort(404);
		}
	
	}
	
	function index($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
			
		$form = $app['form.factory']->create( ActionForm::class );
		
		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
			
				if ($action->getCommand() == 'delete' && !$this->parameters['tag']->getIsDeleted()) {
					$tr = new TagRepository($app);
					$tr->delete($this->parameters['tag'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/tag/'.$this->parameters['tag']->getSlug());

				} else if ($action->getCommand() == 'undelete' && $this->parameters['tag']->getIsDeleted()) {
					$this->parameters['tag']->setIsDeleted(false);
					$tr = new TagRepository($app);
					$tr->undelete($this->parameters['tag'],  $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/tag/'.$this->parameters['tag']->getSlug());


                } else if ($action->getCommand() == 'slughuman') {

                    $this->parameters['tag']->setSlugHuman($action->getParam(0));
                    $tr = new TagRepository($app);
                    $tr->editSlugHuman($this->parameters['tag']);
                    return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/tag/'.$this->parameters['tag']->getSlug());

				}
			}
		}
		
		$this->parameters['form'] = $form->createView();
			
		return $app['twig']->render('sysadmin/tag/index.html.twig', $this->parameters);		
	
	}
	
	
}


