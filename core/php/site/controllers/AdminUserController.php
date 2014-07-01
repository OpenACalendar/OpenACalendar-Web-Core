<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\UserAccountRepository;
use repositories\SiteAccessRequestRepository;
use repositories\UserInSiteRepository;
use repositories\builders\SiteAccessRequestRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminUserController {

	protected $parameters = array();
	
	protected function build($username, Request $request, Application $app) {
		$this->parameters = array();

		$repo = new UserAccountRepository();
		$this->parameters['user'] =  $repo->loadByUserName($username);
		if (!$this->parameters['user']) {
			return false;
		}
		
		return true;

	}

	function request($username, Request $request, Application $app) {
		global $WEBSESSION, $FLASHMESSAGES;
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
				
		if ($request->request->get('action') && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
			$repo = new SiteAccessRequestRepository();
			if ($request->request->get('action') == 'grant') {
				$repo->grantForSiteAndUser($app['currentSite'], $this->parameters['user'], userGetCurrent());
				$this->sendGrantRequestActionEmail($this->parameters['user'], $app['currentSite'], $app);
				$FLASHMESSAGES->addMessage("Request granted.");
			} else if ($request->request->get('action') == 'deny') {
				$repo->rejectForSiteAndUser($app['currentSite'], $this->parameters['user'], userGetCurrent());
				$FLASHMESSAGES->addMessage("Request refused.");
			}
			return $app->redirect("/admin/users");
		}
		
		$repo = new UserInSiteRepository();
		$uis = $repo->loadBySiteAndUserAccount($app['currentSite'], $this->parameters['user']);
		$this->parameters['userIsSiteOwner'] = ($uis && $uis->getIsOwner());
		$this->parameters['userIsSiteAdministrator'] = ($uis && ($uis->getIsOwner() || $uis->getIsAdministrator()));
		$this->parameters['userIsSiteEditor'] = ($uis && ($uis->getIsOwner() || $uis->getIsAdministrator() || $uis->getIsEditor()));
		
		$b = new SiteAccessRequestRepositoryBuilder();
		$b->setSite($app['currentSite']);
		$b->setUser($this->parameters['user']);
		$this->parameters['requests'] = $b->fetchAll();
		
		return $app['twig']->render('site/adminuser/request.html.twig', $this->parameters);
	}
	
	protected function sendGrantRequestActionEmail($user, $site, Application $app) {		
		$message = \Swift_Message::newInstance();
		$message->setSubject("You can now edit ".$site->getTitle());
		$message->setFrom(array($app['config']->emailFrom => $app['config']->emailFromName));
		$message->setTo($user->getEmail());

		$messageText = $app['twig']->render('email/siteAccessRequestGranted.txt.twig', array(
			'user'=>$user,
			'site'=>$site,
		));
		if ($app['config']->isDebug) file_put_contents('/tmp/siteAccessRequestGranted.txt', $messageText);
		$message->setBody($messageText);

		$messageHTML = $app['twig']->render('email/siteAccessRequestGranted.html.twig', array(
			'user'=>$user,
			'site'=>$site,
		));
		if ($app['config']->isDebug) file_put_contents('/tmp/siteAccessRequestGranted.html', $messageHTML);
		$message->addPart($messageHTML,'text/html');

		if (!$app['config']->isDebug) $app['mailer']->send($message);
	
	}
	
	
}


