<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\builders\UserAccountResetRepositoryBuilder;
use repositories\builders\UserAccountVerifyEmailRepositoryBuilder;
use repositories\builders\UserWatchesGroupNotifyEmailRepositoryBuilder;
use repositories\builders\UserWatchesGroupPromptEmailRepositoryBuilder;
use repositories\builders\UserWatchesSiteGroupPromptEmailRepositoryBuilder;
use repositories\builders\UserWatchesSiteNotifyEmailRepositoryBuilder;
use repositories\builders\UserWatchesSitePromptEmailRepositoryBuilder;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;
use repositories\UserAccountVerifyEmailRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserController {
	
	
		
	protected $parameters = array();
	
	protected function build($id, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$uar = new UserAccountRepository();
		$this->parameters['user'] = $uar->loadById($id);
		
		if (!$this->parameters['user']) {
			$app->abort(404);
		}
	
	}
	
	
	function show($id, Request $request, Application $app) {
		global $FLASHMESSAGES;
		
		
		$this->build($id, $request, $app);
		
		
		$form = $app['form.factory']->create(new ActionForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			
			
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
				$uar = new UserAccountRepository();

				if ($action->getCommand() == 'editor' && $action->getParam(0) == 'yes') {
					$this->parameters['user']->setIsEditor(true);
					$uar->edit($this->parameters['user']);
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());
				} else if ($action->getCommand() == 'editor' && $action->getParam(0) == 'no') {
					$this->parameters['user']->setIsEditor(false);
					$uar->edit($this->parameters['user']);
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());
				} else if ($action->getCommand() == 'sysadmin' && $action->getParam(0) == 'yes') {
					$this->parameters['user']->setIsSystemAdmin(true);
					$uar->edit($this->parameters['user']);
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());
				} else if ($action->getCommand() == 'sysadmin' && $action->getParam(0) == 'no') {
					$this->parameters['user']->setIsSystemAdmin(false);
					$uar->edit($this->parameters['user']);
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());
				} else if ($action->getCommand() == 'verifyemail') {
					$uar->verifyEmail($this->parameters['user']);
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());					
				} else if ($action->getCommand() == 'resendverificationemail' && !$this->parameters['user']->getIsEmailVerified()) {
					$repo = new UserAccountVerifyEmailRepository();
					$verify = $repo->create($this->parameters['user']);
					$verify->sendEmail($app, $this->parameters['user']);
					$FLASHMESSAGES->addMessage('Sent');
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());	
				} else if ($action->getCommand() == 'close') {
					$uar->systemAdminShuts($this->parameters['user'], userGetCurrent(), $action->getParam(0));
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());	
				} else if ($action->getCommand() == 'open') {
					$uar->systemAdminOpens($this->parameters['user'], userGetCurrent());
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());			
				}
		
			}
			
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('sysadmin/user/show.html.twig', $this->parameters);
	}
	
	
	function verify($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserAccountVerifyEmailRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['verifies'] = $rb->fetchAll();
		
		return $app['twig']->render('sysadmin/user/verify.html.twig', $this->parameters);
	}
	
	function reset($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserAccountResetRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['resets'] = $rb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/user/reset.html.twig', $this->parameters);
	}
	
	
	function watchesSitePromptEmail($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserWatchesSitePromptEmailRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['emails'] = $rb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/user/watchesSitePromptEmail.html.twig', $this->parameters);
	}
	
	
	function watchesSiteGroupPromptEmail($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserWatchesSiteGroupPromptEmailRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['emails'] = $rb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/user/watchesSiteGroupPromptEmail.html.twig', $this->parameters);
	}
	
	
	function watchesGroupPromptEmail($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserWatchesGroupPromptEmailRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['emails'] = $rb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/user/watchesGroupPromptEmail.html.twig', $this->parameters);
	}
	
	
	function watchesSiteNotifyEmail($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserWatchesSiteNotifyEmailRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['emails'] = $rb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/user/watchesSiteNotifyEmail.html.twig', $this->parameters);
	}
	
	
	function watchesGroupNotifyEmail($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
		
		
		$rb = new UserWatchesGroupNotifyEmailRepositoryBuilder();
		$rb->setUser($this->parameters['user']);
		$this->parameters['emails'] = $rb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/user/watchesGroupNotifyEmail.html.twig', $this->parameters);
	}
	
}


