<?php

namespace sysadmin\controllers;

use repositories\builders\AreaRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\builders\SysadminCommentRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use repositories\SysAdminCommentRepository;
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
use repositories\builders\UserNotificationRepositoryBuilder;
use sysadmin\forms\ActionWithCommentForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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
		
		$this->build($id, $request, $app);
		
		
		$form = $app['form.factory']->create(new ActionWithCommentForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			
			
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
				$uar = new UserAccountRepository();

				$redirect = false;

				if ($data['comment']) {
					$scr = new SysAdminCommentRepository();
					$scr->createAboutUser($this->parameters['user'], $data['comment'], $app['currentUser']);
					$redirect = true;
				}

				if ($action->getCommand() == 'editor' && $action->getParam(0) == 'yes') {
					$this->parameters['user']->setIsEditor(true);
					$uar->edit($this->parameters['user']);
					$redirect = true;
				} else if ($action->getCommand() == 'editor' && $action->getParam(0) == 'no') {
					$this->parameters['user']->setIsEditor(false);
					$uar->edit($this->parameters['user']);
					$redirect = true;
				} else if ($action->getCommand() == 'sysadmin' && $action->getParam(0) == 'yes') {
					$this->parameters['user']->setIsSystemAdmin(true);
					$uar->edit($this->parameters['user']);
					$redirect = true;
				} else if ($action->getCommand() == 'sysadmin' && $action->getParam(0) == 'no') {
					$this->parameters['user']->setIsSystemAdmin(false);
					$uar->edit($this->parameters['user']);
					$redirect = true;
				} else if ($action->getCommand() == 'verifyemail') {
					$uar->verifyEmail($this->parameters['user']);
					$redirect = true;
				} else if ($action->getCommand() == 'resendverificationemail' && !$this->parameters['user']->getIsEmailVerified()) {
					$repo = new UserAccountVerifyEmailRepository();
					$verify = $repo->create($this->parameters['user']);
					$verify->sendEmail($app, $this->parameters['user']);
					$app['flashmessages']->addMessage('Sent');
					$redirect = true;
				} else if ($action->getCommand() == 'close') {
					$uar->systemAdminShuts($this->parameters['user'], $app['currentUser'], $action->getParam(0));
					$redirect = true;
				} else if ($action->getCommand() == 'open') {
					$uar->systemAdminOpens($this->parameters['user'], $app['currentUser']);
					$redirect = true;
				} else if ($action->getCommand() == 'email' && filter_var($action->getParam(0), FILTER_VALIDATE_EMAIL)) {
					$this->parameters['user']->setEmail($action->getParam(0));
					$uar->editEmail($this->parameters['user']);
					$redirect = true;
				}

				if ($redirect) {
					return $app->redirect('/sysadmin/user/'.$this->parameters['user']->getId());
				}
		
			}
			
		}
		
		$this->parameters['form'] = $form->createView();

		$sacrb = new SysadminCommentRepositoryBuilder();
		$sacrb->setUser($this->parameters['user']);
		$this->parameters['comments'] = $sacrb->fetchAll();

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

	
	function listNotifications($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
	
		$rb = new UserNotificationRepositoryBuilder($app['extensions']);
		$rb->setLimit(40);
		$rb->setUser($this->parameters['user']);
		$this->parameters['notifications'] = $rb->fetchAll();

		return $app['twig']->render('/sysadmin/user/notifications.html.twig', $this->parameters);
	}

	function eventEdited($id, Request $request, Application $app) {
		$this->build($id, $request, $app);

		$erb = new EventRepositoryBuilder();
		$erb->setEditedByUser($this->parameters['user']);
		$erb->setOrderByStartAt(true);
		$this->parameters['events'] = $erb->fetchAll();



		return $app['twig']->render('/sysadmin/user/event.edited.html.twig', $this->parameters);
	}

	function areaEdited($id, Request $request, Application $app) {
		$this->build($id, $request, $app);

		$arb = new AreaRepositoryBuilder();
		$arb->setEditedByUser($this->parameters['user']);
		$this->parameters['areas'] = $arb->fetchAll();



		return $app['twig']->render('/sysadmin/user/area.edited.html.twig', $this->parameters);
	}

	function venueEdited($id, Request $request, Application $app) {
		$this->build($id, $request, $app);

		$vrb = new VenueRepositoryBuilder();
		$vrb->setEditedByUser($this->parameters['user']);
		$this->parameters['venues'] = $vrb->fetchAll();



		return $app['twig']->render('/sysadmin/user/venue.edited.html.twig', $this->parameters);
	}

	function groupEdited($id, Request $request, Application $app) {
		$this->build($id, $request, $app);

		$grb = new GroupRepositoryBuilder();
		$grb->setEditedByUser($this->parameters['user']);
		$this->parameters['groups'] = $grb->fetchAll();



		return $app['twig']->render('/sysadmin/user/group.edited.html.twig', $this->parameters);
	}
}


