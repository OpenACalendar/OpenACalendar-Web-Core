<?php

namespace site\controllers;

use Silex\Application;
use index\forms\CreateForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\CountryModel;
use repositories\CountryRepository;
use repositories\UserAccountRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\SiteAccessRequestRepository;
use repositories\builders\UserAccountRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class IndexController {
	
	function index(Application $app) {
		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
		$erb->setAfterNow();
		$erb->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$erb->setUserAccount(userGetCurrent(), true);
		}		
		$events = $erb->fetchAll();
		
		return $app['twig']->render('site/index/index.html.twig', array(
				'events'=>$events,
			));
		
	}
	
	function myTimeZone(Application $app) {
		global $CONFIG;
		
		return $app['twig']->render('site/index/myTimeZone.html.twig', array(
			));
	}
	
	
	
	function watch(Application $app) {
		global $CONFIG, $WEBSESSION;
		
		if (isset($_POST['action'])  && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			$repo = new UserWatchesSiteRepository();
			if ($_POST['action'] == 'watch') {
				$repo->startUserWatchingSite(userGetCurrent(), $app['currentSite']);
			} else if ($_POST['action'] == 'unwatch') {
				$repo->stopUserWatchingSite(userGetCurrent(), $app['currentSite']);
			}
			// redirect here because if we didn't the twig global and $app vars would be wrong (the old state)
			// this is an easy way to get round that.
			return $app->redirect('/watch');
		}
		
		return $app['twig']->render('site/index/watch.html.twig', array(
			));
	}
	
	function stopWatchingFromEmail($userid, $code, Application $app) {
		global $FLASHMESSAGES, $WEBSESSION;
		
		$userRepo = new UserAccountRepository();
		$user = $userRepo->loadByID($userid);
		if (!$user) {
			$app['monolog']->addError("Failed stop watching site from email - user not known");
			die("NO"); // TODO
		}
		
		$userWatchesSiteStopRepo = new UserWatchesSiteStopRepository();
		$userWatchesSiteStop = $userWatchesSiteStopRepo->loadByUserAccountIDAndSiteIDAndAccessKey($user->getId(), $app['currentSite']->getId(), $code);
		if (!$userWatchesSiteStop) {
			$app['monolog']->addError("Failed stop watching site from email - user ".$user->getId()." - code wrong");
			die("NO"); // TODO
		}
		
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepo->loadByUserAndSite($user, $app['currentSite']);
		if (!$userWatchesSite || !$userWatchesSite->getIsWatching()) {
			$app['monolog']->addError("Failed stop watching site from email - user ".$user->getId()." - not watching");
			die("You don't watch this site"); // TODO
		}
		
		if (isset($_POST['action']) && $_POST['action'] == 'unwatch' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			$userWatchesSiteRepo->stopUserWatchingSite($user, $app['currentSite']);
			// redirect here because if we didn't the twig global and $app vars would be wrong (the old state)
			// this is an easy way to get round that.
			$FLASHMESSAGES->addMessage("You have stopped watching this.");
			return $app->redirect('/');
		}
		
		return $app['twig']->render('site/index/stopWatchingFromEmail.html.twig', array(
				'user'=>$user,
			));
		
	}
	
	
	function requestAccess(Application $app) {
		global $CONFIG, $WEBSESSION, $FLASHMESSAGES;
		
		if (isset($_POST['CSFRToken'])  && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			
			$repo = new SiteAccessRequestRepository();
			$isCurrentRequestExistsForSiteAndUser = $repo->isCurrentRequestExistsForSiteAndUser($app['currentSite'], userGetCurrent());
			$repo->create($app['currentSite'], userGetCurrent(), $_POST['answer']);
			
			if (!$isCurrentRequestExistsForSiteAndUser) {
				
				$urb = new UserAccountRepositoryBuilder();
				$urb->setCanAdministrateSite($app['currentSite']);
				foreach($urb->fetchAll() as $admin) {
					
					$message = \Swift_Message::newInstance();
					$message->setSubject("A request to access ". $app['currentSite']->getTitle());
					$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
					$message->setTo($admin->getEmail());

					$messageText = $app['twig']->render('email/requestAccess.txt.twig', array(
						'user'=>  userGetCurrent(),
						'admin'=>  $admin,
						'answer'=>  $_POST['answer'],
					));
					if ($CONFIG->isDebug) file_put_contents('/tmp/requestAccess.txt', $messageText);
					$message->setBody($messageText);

					$messageHTML = $app['twig']->render('email/requestAccess.html.twig', array(
						'user'=>userGetCurrent(),
						'admin'=>  $admin,
						'answer'=>  $_POST['answer'],
					));
					if ($CONFIG->isDebug) file_put_contents('/tmp/requestAccess.html', $messageHTML);
					$message->addPart($messageHTML,'text/html');

					if (!$CONFIG->isDebug) $app['mailer']->send($message);
				}
				
			}
			
			return $app['twig']->render('site/index/requestaccess.done.html.twig', array());
			
		}

		return $app->redirect('/');
		
	}

	
	function places(Application $app) {
		global $CONFIG;
		
		return $app['twig']->render('site/index/places.html.twig', array(
			));
	}
	
	
	function currentUser(Application $app) {
		global $CONFIG;
		
		if (userGetCurrent()) {
			return $app['twig']->render('site/index/currentUser.user.html.twig', array(
				));
		} else {
			return $app['twig']->render('site/index/currentUser.anon.html.twig', array(
				));
		}
	}
	
}


