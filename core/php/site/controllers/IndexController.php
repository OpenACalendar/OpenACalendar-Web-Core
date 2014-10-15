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
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserNotificationRepository;

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
		$erb->setIncludeAreaInformation(true);
		$erb->setIncludeVenueInformation(true);
		if (userGetCurrent()) {
			$erb->setUserAccount(userGetCurrent(), true);
		}		
		$events = $erb->fetchAll();
		
		return $app['twig']->render('site/index/index.html.twig', array(
				'events'=>$events,
			));
		
	}
	
	function myTimeZone(Application $app) {
		return $app['twig']->render('site/index/myTimeZone.html.twig', array(
			));
	}
	
	
	
	function watch(Request $request, Application $app) {		
		if ($request->request->get('action')  && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$repo = new UserWatchesSiteRepository();
			if ($request->request->get('action') == 'watch') {
				$repo->startUserWatchingSite(userGetCurrent(), $app['currentSite']);
			} else if ($request->request->get('action') == 'unwatch') {
				$repo->stopUserWatchingSite(userGetCurrent(), $app['currentSite']);
			}
			// redirect here because if we didn't the twig global and $app vars would be wrong (the old state)
			// this is an easy way to get round that.
			return $app->redirect('/watch');
		}
		
		return $app['twig']->render('site/index/watch.html.twig', array(
			));
	}
	
	function stopWatchingFromEmail($userid, $code, Request $request, Application $app) {		
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
		
		if ($request->request->get('action') == 'unwatch' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$userWatchesSiteRepo->stopUserWatchingSite($user, $app['currentSite']);
			// redirect here because if we didn't the twig global and $app vars would be wrong (the old state)
			// this is an easy way to get round that.
			$app['flashmessages']->addMessage("You have stopped watching this.");
			return $app->redirect('/');
		}
		
		return $app['twig']->render('site/index/stopWatchingFromEmail.html.twig', array(
				'user'=>$user,
			));
		
	}
	


	
	function places(Application $app) {		
		return $app['twig']->render('site/index/places.html.twig', array(
			));
	}
	
	
	function currentUser(Application $app) {		
		if (userGetCurrent()) {
			return $app['twig']->render('site/index/currentUser.user.html.twig', array(
				));
		} else {
			return $app['twig']->render('site/index/currentUser.anon.html.twig', array(
				));
		}
	}
	
}


