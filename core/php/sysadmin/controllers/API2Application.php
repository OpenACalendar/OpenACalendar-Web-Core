<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\API2ApplicationRepository;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;
use repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2Application {
	
	
	protected $parameters = array('api2Application'=>null);
	
	protected function build($id, Request $request, Application $app) {
		$repo = new API2ApplicationRepository();
		$this->parameters['api2Application'] = $repo->loadById($id);
		if (!$this->parameters['api2Application']) {
			$app->abort(404);
		}
	}
	
	function show($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
				
		$form = $app['form.factory']->create(new ActionForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
				$api2appRepo = new \repositories\API2ApplicationRepository();
			
				if ($action->getCommand() == 'close') {
					$this->parameters['api2Application']->setIsClosedBySysAdmin(true);
					$this->parameters['api2Application']->setClosedBySysAdminreason($action->getParam(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
					
				} else if ($action->getCommand() == 'open') {
					$this->parameters['api2Application']->setIsClosedBySysAdmin(false);
					$this->parameters['api2Application']->setClosedBySysAdminreason(null);
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
				
			
				} else if ($action->getCommand() == 'autoapprove') {
					$this->parameters['api2Application']->setIsAutoApprove($action->getParamBoolean(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
					
				} else if ($action->getCommand() == 'permissioneditor') {
					$this->parameters['api2Application']->setIsEditor($action->getParamBoolean(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());

				} else if ($action->getCommand() == 'iscallbackdisplay') {
					$this->parameters['api2Application']->setIsCallbackDisplay($action->getParamBoolean(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
				
				} else if ($action->getCommand() == 'iscallbackjavascript') {
					$this->parameters['api2Application']->setIsCallbackJavascript($action->getParamBoolean(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
				
				} else if ($action->getCommand() == 'iscallbackurl') {
					$this->parameters['api2Application']->setIsCallbackUrl($action->getParamBoolean(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
				
				} else if ($action->getCommand() == 'addcallbackurl') {
					$this->parameters['api2Application']->addAllowedCallbackUrl($action->getParam(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
					
				} else if ($action->getCommand() == 'removecallbackurl') {
					$this->parameters['api2Application']->removeAllowedCallbackUrl($action->getParam(0));
					$api2appRepo->edit($this->parameters['api2Application'], userGetCurrent());
					return $app->redirect('/sysadmin/api2app/'.$this->parameters['api2Application']->getId());
				
					
				}
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('sysadmin/api2app/show.html.twig', $this->parameters);		
	
	}

	function history($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		$hrb = new HistoryRepositoryBuilder();
		$hrb->setAPI2Application($this->parameters['api2Application']);
		$this->parameters['historyItems']= $hrb->fetchAll();

		return $app['twig']->render('sysadmin/api2app/history.html.twig', $this->parameters);
		
	}

}
