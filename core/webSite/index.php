<?php

use repositories\UserHasNoEditorPermissionsInSiteRepository;
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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->before(function (Request $request) use ($app) {
	# ////////////// Site
	$siteRepository = new SiteRepository($app);
	$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);
	if (!$site) {
		die ("404 Not Found"); // TODO
	}

	$app['twig']->addGlobal('currentSite', $site);	
	$app['currentSite'] = $site;

    # ////////////// Force SSL?
    if ($app['config']->hasSSL && $app['config']->forceSSL) {
        $fr = new ForceRequestToSSL($app);
        $fr->processForSite($site);
        unset($fr);
    }

    # ////////////// Site closed
	if ($app['currentSite']->getIsClosedBySysAdmin()) {
		$app['twig']->addGlobal('currentUserInSite', null);
		$app['twig']->addGlobal('currentUserCanAdminSite', false);
		$app['twig']->addGlobal('currentUserCanEditSite', false);
		$app['twig']->addGlobal('currentSiteFeatures', new SiteFeaturesList(array()));
		$app['twig']->addGlobal('currentUserActions', new UserActionsSiteList($app['currentSite'], new UserPermissionsList($app['extensions'], array())));
		return new Response($app['twig']->render('site/closed_by_sys_admin.html.twig', array()));
	}

	# ////////////// Features
	$siteFeaturesRepo = new repositories\SiteFeatureRepository($app);
	$app['currentSiteFeatures'] = new SiteFeaturesList($siteFeaturesRepo->getForSiteAsTree($app['currentSite']));
	$app['twig']->addGlobal('currentSiteFeatures', $app['currentSiteFeatures']);
	$app['currentSiteFeatures']->setFeaturesOnSite($app['currentSite']);

	# ////////////// Permissions and Watch
	$userPermissionsRepo = new \repositories\UserPermissionsRepository($app);
	$removeEditorPermissions = false;
	$userHasNoEditorPermissionsInSiteRepo = new UserHasNoEditorPermissionsInSiteRepository($app);
	if ($app['currentUser'] && $userHasNoEditorPermissionsInSiteRepo->isUserInSite($app['currentUser'], $app['currentSite'])) {
		$removeEditorPermissions = true;
	}
	$app['currentUserPermissions'] = $userPermissionsRepo->getPermissionsForUserInSite($app['currentUser'], $app['currentSite'], $removeEditorPermissions, true);


	# ////////////// User and their watch and perms
	$app['currentUserActions'] = new UserActionsSiteList($app['currentSite'], $app['currentUserPermissions']);
	$app['currentUserWatchesSite'] = false;
	if ($app['currentUser']) {
		$uwsr = new UserWatchesSiteRepository($app);
		$uws = $uwsr->loadByUserAndSite($app['currentUser'], $app['currentSite']);
		$app['currentUserWatchesSite'] = $uws && $uws->getIsWatching();
	}
	$app['twig']->addGlobal('currentUserActions', $app['currentUserActions']);
	$app['twig']->addGlobal('currentUserWatchesSite', $app['currentUserWatchesSite']);

	# ////////////// if not current user, let templates see what currentUser could do
	if (!$app['currentUser']) {
		// We don't pass $removeEditorPermissions here because that is about specific users being banned and this is potential users
		$app['anyVerifiedUserPermissions'] = $userPermissionsRepo->getPermissionsForAnyVerifiedUserInSite($app['currentSite'], false, true);
		$app['anyVerifiedUserActions'] = new UserActionsSiteList($app['currentSite'], $app['anyVerifiedUserPermissions'] );
		$app['twig']->addGlobal('anyVerifiedUserActions', $app['anyVerifiedUserActions'] );
	}

	# ////////////// Store sites seen for this user so can do nice page in index
	// except we don't bother doing this in the API
	if (substr($_SERVER['REQUEST_URI'],0,4) != '/api') {
		// we do this in cookies not session because it's not security sensitive and it saves us making a session for every bot that visits
		if (!isset($_COOKIE['sitesSeen'])) {
			setcookie("sitesSeen",$site->getId(),time()+60*60*24*$app['config']->siteSeenCookieStoreForDays,'/',$app['config']->webCommonSessionDomain,false,false);
		} else {
			$vals = explode(",",  $_COOKIE['sitesSeen']);
			if (!in_array($site->getId(), $vals)) {
				$vals[] = $site->getId();
			}
			setcookie("sitesSeen",  implode(",", $vals),time()+60*60*24*$app['config']->siteSeenCookieStoreForDays,'/',$app['config']->webCommonSessionDomain,false,false);
		}
	}
	
	# ////////////// Timezone
	$timezone = "";
	if (isset($_GET['mytimezone']) && in_array($_GET['mytimezone'], $app['currentSite']->getCachedTimezonesAsList())) {
		setcookie("site".$app['currentSite']->getId()."timezone",$_GET['mytimezone'],time()+60*60*24*365,'/',$app['config']->webCommonSessionDomain,false,false);
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
		$cr = new CountryRepository($app);
		$app['currentSiteHasOneCountry'] = $cr->loadBySite($app['currentSite']);
		$app['twig']->addGlobal('currentSiteHasOneCountry', $app['currentSiteHasOneCountry']);	
	}

	# ////////////// Misc
	header("X-Pingback: ". $app['config']->getWebSiteDomainSecure($app['currentSite']->getSlug())."/receivepingback.php");
});


$permissionCalendarChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$permissionCalendarChangeRequiredOrForAnyVerifiedUser = function(Request $request, Application $app) {
	if ($app['currentUser']) {
		if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
			return $app->abort(403); // TODO
		}
	} else {
		if (!$app['anyVerifiedUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}
	}
};

$permissionCalendarAdministratorRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}
	}
};


$permissionEventsChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};


$permissionEventsChangeRequiredOrForAnyVerifiedUser = function(Request $request, Application $app) {
	if ($app['currentUser']) {
		if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")) {
			return $app->abort(403); // TODO
		}
	} else {
		if (!$app['anyVerifiedUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")) {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}
	}
};

$permissionGroupsChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","GROUPS_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$permissionGroupsChangeRequiredOrForAnyVerifiedUser = function(Request $request, Application $app) {
	if ($app['currentUser']) {
		if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","GROUPS_CHANGE")) {
			return $app->abort(403); // TODO
		}
	} else {
		if (!$app['anyVerifiedUserPermissions']->hasPermission("org.openacalendar","GROUPS_CHANGE")) {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}
	}
};

$permissionVenuesChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","VENUES_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$permissionAreasChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","AREAS_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$permissionTagsChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","TAGS_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$permissionImportURLsChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","IMPORTURL_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$permissionMediasChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","MEDIAS_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
			return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
		}

	}
};

$appUserRequired = function(Request $request) use ($app) {
	if (!$app['currentUser']) {
		return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
	}
};

$appVerifiedUserRequired = function(Request $request) use ($app) {
	if (!$app['currentUser']) {
		return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
	}
	if (!$app['currentUser']->getIsEmailVerified()) {
		return new RedirectResponse('http://'.$app['config']->webIndexDomain.'/me/verifyneeded');
	}
};

$featureCuratedListRequired = function(Request $request)  use ($app) {
	if (!$app['currentSite']->getIsFeatureCuratedList()) {
		return new RedirectResponse('/curatedlist');
	}
};

$featureGroupRequired = function(Request $request)  use ($app) {
	if (!$app['currentSite']->getIsFeatureGroup()) {
		return new RedirectResponse('/group');
	}
};


$featureTagRequired = function(Request $request)  use ($app) {
	if (!$app['currentSite']->getIsFeatureTag()) {
		return new RedirectResponse('/tag');
	}
};


$featureImporterRequired = function(Request $request)  use ($app) {
	if (!$app['currentSite']->getIsFeatureImporter()) {
		return new RedirectResponse('/importurl');
	}
};

$featurePhysicalEventsRequired = function(Request $request)  use ($app) {
	if (!$app['currentSite']->getIsFeaturePhysicalEvents()) {
		return new RedirectResponse('/venue');
	}
};

$appFileStoreRequired = function(Request $request)  use ($app) {
	if (!$app['config']->isFileStore()) {
		return new RedirectResponse('/');
	}
};

$canChangeSite = function(Request $request) use ($app) {
	if ($app['config']->siteReadOnly) {
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


