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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

if(!$CONFIG->isSingleSiteMode) {
	die("Single Site Mode Not Enabled");
}

# ////////////// Site
$siteRepository = new SiteRepository($app);
$site = $siteRepository->loadById($app['config']->singleSiteID);
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
    print $app['twig']->render('site/closed_by_sys_admin.html.twig', array());
    die();
}

# ////////////// Features
$siteFeaturesRepo = new repositories\SiteFeatureRepository($app);
$app['currentSiteFeatures'] = new SiteFeaturesList($siteFeaturesRepo->getForSiteAsTree($app['currentSite']));
$app['twig']->addGlobal('currentSiteFeatures', $app['currentSiteFeatures']);

# ////////////// Permissions and Watch
$userPermissionsRepo = new \repositories\UserPermissionsRepository($app);
// We do not check UserHasNoEditorPermissionsInSiteRepository(); because that is site mode only.
// In Single Site mode sysadmins can remove this right.
$app['currentUserPermissions'] = $userPermissionsRepo->getPermissionsForUserInSite($app['currentUser'], $app['currentSite'], false, true);


# ////////////// User and their watch and perms
$app['currentUserActions'] = new UserActionsSiteList($app, $app['currentSite'], $app['currentUserPermissions']);
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
    $app['anyVerifiedUserActions'] = new UserActionsSiteList($app, $app['currentSite'], $app['anyVerifiedUserPermissions'] );
    $app['twig']->addGlobal('anyVerifiedUserActions', $app['anyVerifiedUserActions'] );
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
$app['twig']->addGlobal('allowedTimeZones', $app['currentSite']->getCachedTimezonesAsList());
$app['currentTimeZone'] = $timezone;

# ////////////// Country
if (!$app['currentSite']->getCachedIsMultipleCountries()) {
    $cr = new CountryRepository($app);
    $app['currentSiteHasOneCountry'] = $cr->loadBySite($app['currentSite']);
    $app['twig']->addGlobal('currentSiteHasOneCountry', $app['currentSiteHasOneCountry']);
} else {
    $app['currentSiteHasOneCountry'] = null;
}

# ////////////// Misc
header("X-Pingback: ". $app['config']->getWebSiteDomainSecure($app['currentSite']->getSlug())."/receivepingback.php");


# ////////////// Route Functions

$permissionCalendarChangeRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
		if ($app['currentUser']) {
			return $app->abort(403); // TODO
		} else {
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

$appUnverifiedUserRequired = function(Request $request) use ($app) {
	if (!$app['currentUser']) {
		return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
	}
	if ($app['currentUser']->getIsEmailVerified()) {
		return new RedirectResponse('/');
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
	if (!$app['currentSiteFeatures']->has('org.openacalendar.curatedlists','CuratedList')) {
		return new RedirectResponse('/curatedlist');
	}
};

$featureGroupRequired = function(Request $request)  use ($app) {
	if (!$app['currentSiteFeatures']->has('org.openacalendar','Group')) {
		return new RedirectResponse('/group');
	}
};

$featureTagRequired = function(Request $request)  use ($app) {
	if (!$app['currentSiteFeatures']->has('org.openacalendar','Tag')) {
		return new RedirectResponse('/tag');
	}
};

$featureImporterRequired = function(Request $request)  use ($app) {
	if (!$app['currentSiteFeatures']->has('org.openacalendar','Importer')) {
		return new RedirectResponse('/importurl');
	}
};

$featurePhysicalEventsRequired = function(Request $request)  use ($app) {
	if (!$app['currentSiteFeatures']->has('org.openacalendar','PhysicalEvents')) {
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

# ////////////// Routes
define('FRIENDLY_SLUG_REGEX','\d[a-z\d\-]*');

$app->match('/', "site\controllers\IndexController::index") ; 

require APP_ROOT_DIR.'/core/webIndex/index.routes.php';
require APP_ROOT_DIR.'/core/webSite/index.routes.php';

# ////////////// Errors
if (!$CONFIG->isDebug) {
	$app->error(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $code) use ($app) {
		if ($e->getStatusCode() == 404) {
            header("HTTP/1.0 404 Not Found");
			return new Response($app['twig']->render('site/error404.html.twig', array('exception'=>$e)));
		} else {
			return new Response($app['twig']->render('site/error.html.twig', array('exception'=>$e)));
		}
	});
}

# ////////////// Extensions
foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSingleSite/index.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSingleSite/index.routes.php';
	}
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/index.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/index.routes.php';
	}
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webIndex/index.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webIndex/index.routes.php';
	}
}

# ////////////// GO!
$app->run(); 


