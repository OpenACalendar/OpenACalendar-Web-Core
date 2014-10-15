<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\SiteRepository;
use repositories\UserInSiteRepository;
use repositories\UserWatchesSiteRepository;
use repositories\CountryRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->before(function (Request $request) use ($app) {
	global $CONFIG, $WEBSESSION;
	# ////////////// Site
	$siteRepository = new SiteRepository();
	$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);
	if (!$site) {
		die ("404 Not Found"); // TODO
	}
	
	$app['twig']->addGlobal('currentSite', $site);	
	$app['currentSite'] = $site;
	
	# ////////////// Site closed
	if ($app['currentSite']->getIsClosedBySysAdmin()) {
		$app['twig']->addGlobal('currentUserInSite', null);
		$app['twig']->addGlobal('currentUserCanAdminSite', false);
		$app['twig']->addGlobal('currentUserCanEditSite', false);
		return new Response($app['twig']->render('site/closed_by_sys_admin.html.twig', array()));
	}

	# ////////////// Permissions and Watch
	$userPermissionsRepo = new \repositories\UserPermissionsRepository($app['extensions']);
	$app['currentUserPermissions'] = $userPermissionsRepo->getPermissionsForUserInSite(userGetCurrent(), $app['currentSite'], false, true);


	# ////////////// User and their watch and perms
	$app['currentUserActions'] = new UserActionsSiteList($app['currentSite'], $app['currentUserPermissions']);
	if (userGetCurrent()) {
		$uwsr = new UserWatchesSiteRepository();
		$uws = $uwsr->loadByUserAndSite(userGetCurrent(), $app['currentSite']);
		$app['currentUserWatchesSite'] = $uws && $uws->getIsWatching();
	}
	$app['twig']->addGlobal('currentUserActions', $app['currentUserActions']);
	$app['twig']->addGlobal('currentUserWatchesSite', $app['currentUserWatchesSite']);

	# ////////////// Store sites seen for this user so can do nice page in index
	// except we don't bother doing this in the API
	if (substr($_SERVER['REQUEST_URI'],0,4) != '/api') {
		// we do this in cookies not session because it's not security sensitive and it saves us making a session for every bot that visits
		if (!isset($_COOKIE['sitesSeen'])) {
			setcookie("sitesSeen",$site->getId(),time()+60*60*24*$CONFIG->siteSeenCookieStoreForDays,'/',$CONFIG->webCommonSessionDomain,false,false);
		} else {
			$vals = explode(",",  $_COOKIE['sitesSeen']);
			if (!in_array($site->getId(), $vals)) {
				$vals[] = $site->getId();
			}
			setcookie("sitesSeen",  implode(",", $vals),time()+60*60*24*$CONFIG->siteSeenCookieStoreForDays,'/',$CONFIG->webCommonSessionDomain,false,false);
		}
	}
	
	# ////////////// Timezone
	$timezone = "";
	if (isset($_GET['mytimezone']) && in_array($_GET['mytimezone'], $app['currentSite']->getCachedTimezonesAsList())) {
		setcookie("site".$app['currentSite']->getId()."timezone",$_GET['mytimezone'],time()+60*60*24*365,'/',$CONFIG->webCommonSessionDomain,false,false);
		$timezone = $_GET['mytimezone'];
	} else if (isset($_COOKIE["site".$app['currentSite']->getId()."timezone"]) && in_array($_COOKIE["site".$app['currentSite']->getId()."timezone"],$site->getCachedTimezonesAsList())) {
		$timezone = $_COOKIE["site".$app['currentSite']->getId()."timezone"];
	} else if (in_array('Europe/London',$site->getCachedTimezonesAsList())) {
		$timezone = 'Europe/London';
	} else {
		$timezone  = $site->getCachedTimezonesAsList()[0];
	}
	$app['twig']->addGlobal('currentTimeZone', $timezone);	
	$app['currentTimeZone'] = $timezone;
	
	# ////////////// Country
	if (!$app['currentSite']->getCachedIsMultipleCountries()) {
		$cr = new CountryRepository();
		$app['currentSiteHasOneCountry'] = $cr->loadBySite($app['currentSite']);
		$app['twig']->addGlobal('currentSiteHasOneCountry', $app['currentSiteHasOneCountry']);	
	}
	
});


$permissionCalendarEditRequired = function(Request $request, Application $app) {
	global $CONFIG;
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
		return $app->abort(403); // TODO
	}
};

$permissionCalendarAdministratorRequired = function(Request $request, Application $app) {
	global $CONFIG;
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE")) {
		return $app->abort(403); // TODO
	}
};



$appUserRequired = function(Request $request) {
	global $CONFIG;
	if (!userGetCurrent()) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
};

$appVerifiedUserRequired = function(Request $request) {
	global $CONFIG;	
	if (!userGetCurrent()) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
	if (!userGetCurrent()->getIsEmailVerified()) {
		return new RedirectResponse('http://'.$CONFIG->webIndexDomain.'/me/verifyneeded');
	}
};

$featureCuratedListRequired = function(Request $request)  use ($app) {
	global $CONFIG;
	if (!$app['currentSite']->getIsFeatureCuratedList()) {
		return new RedirectResponse('/curatedlist');
	}
};

$featureGroupRequired = function(Request $request)  use ($app) {
	global $CONFIG;
	if (!$app['currentSite']->getIsFeatureGroup()) {
		return new RedirectResponse('/group');
	}
};


$featureTagRequired = function(Request $request)  use ($app) {
	global $CONFIG;
	if (!$app['currentSite']->getIsFeatureTag()) {
		return new RedirectResponse('/tag');
	}
};


$featureImporterRequired = function(Request $request)  use ($app) {
	global $CONFIG;
	if (!$app['currentSite']->getIsFeatureImporter()) {
		return new RedirectResponse('/importurl');
	}
};

$featurePhysicalEventsRequired = function(Request $request)  use ($app) {
	global $CONFIG;
	if (!$app['currentSite']->getIsFeaturePhysicalEvents()) {
		return new RedirectResponse('/venue');
	}
};

$appFileStoreRequired = function(Request $request)  use ($app) {
	global $CONFIG;
	if (!$CONFIG->isFileStore()) {
		return new RedirectResponse('/');
	}
};

$canChangeSite = function(Request $request) use ($app) {
	global  $CONFIG;
	if ($CONFIG->siteReadOnly) {
		return new Response($app['twig']->render('site/readonly.html.twig', array()));
	}		
};

define('FRIENDLY_SLUG_REGEX','\d[a-z\d\-]*');

$app->match('/', "site\controllers\IndexController::index") ; 

require APP_ROOT_DIR.'/core/webSite/index.routes.php';


if (!$CONFIG->isDebug) {
	$app->error(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $code) use ($app) {
		if ($e->getStatusCode() == 404) {
			return new Response($app['twig']->render('site/error404.html.twig', array('exception'=>$e)));
		} else {
			return new Response($app['twig']->render('site/error.html.twig', array('exception'=>$e)));
		}
	});
}

foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/index.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/index.routes.php';
	}
}


$app->run(); 


