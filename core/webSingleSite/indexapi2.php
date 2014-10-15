<?php


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\API2ApplicationRepository;
use repositories\API2ApplicationUserTokenRepository;
use repositories\UserInSiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

if(!$CONFIG->isSingleSiteMode) {
	die("Single Site Mode Not Enabled");
}

$app->before(function (Request $request) use ($app) {
	global $CONFIG, $WEBSESSION;

	# ////////////// Site
	$siteRepository = new SiteRepository();
	$site = $siteRepository->loadById($CONFIG->singleSiteID);
	if (!$site) {
		die ("404 Not Found"); // TODO
	}

	$app['twig']->addGlobal('currentSite', $site);
	$app['currentSite'] = $site;

	# ////////////// Site closed
	if ($app['currentSite']->getIsClosedBySysAdmin()) {
		return new Response($app['twig']->render('site/closed_by_sys_admin.html.twig', array()));
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

	# /////////////// Permissions

	// App and user?
	$data = array_merge(array('app_token'=>null,'app_secret'=>null,'user_token'=>null,'user_secret'=>null),$_POST, $_GET);
	$app['apiApp'] = null;
	$app['apiAppLoadedBySecret'] = false;
	$app['apiUser'] = null;
	$app['apiUserToken'] = null;
	$appRepo = new API2ApplicationRepository();
	if ($data['app_secret']) {
		$apiapp = $appRepo->loadByAppTokenAndAppSecret($data['app_token'], $data['app_secret']);
		$app['apiAppLoadedBySecret'] = true;
	} else {
		$apiapp = $appRepo->loadByAppToken($data['app_token']);
	}
	if ($apiapp && !$apiapp->getIsClosedBySysAdmin()) {

		$app['apiApp'] = $apiapp;
		$app['userAgent']->setApi2ApplicationId($apiapp->getId());

		// User Token
		$userTokenRepo = new API2ApplicationUserTokenRepository();
		if ($data['user_token']) {
			$app['apiUserToken'] = $userTokenRepo->loadByAppAndUserTokenAndUserSecret($apiapp, $data['user_token'], $data['user_secret']);
			if ($app['apiUserToken']) {

				// User
				$userRepo = new UserAccountRepository();
				$app['apiUser'] = $userRepo->loadByID($app['apiUserToken']->getUserId());

			}
		}

	}

	// user permissons
	$userPermissionsRepo = new \repositories\UserPermissionsRepository($app['extensions']);
	// if app is not editor or token is not editor, remove edit permissions
	$removeEditPermissions =
		($app['apiApp'] && !$app['apiApp']->getIsEditor()) ||
		($app['apiUserToken'] && !$app['apiUserToken']->getIsEditor());
	$app['currentUserPermissions'] = $userPermissionsRepo->getPermissionsForUserInSite($app['apiUser'], $app['currentSite'], $removeEditPermissions, true);

	// finally user actions
	$app['currentUserActions'] = new UserActionsSiteList($app['currentSite'], $app['currentUserPermissions']);

});

$appUserRequired = function(Request $request) use ($app) {

	if (!$app['apiUser']) {
		// TODO also if app closed
		die("ERROR"); // TODO something better
	}

};

$appUserPermissionCalendarChangeRequired = function(Request $request) use ($app) {
	if (!$app['apiUser']) {
		// TODO also if app closed
		die("ERROR"); // TODO something better
	}
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
		return $app->abort(403); // TODO
	}
};


require APP_ROOT_DIR.'/core/webSite/indexapi2.routes.php';
require APP_ROOT_DIR.'/core/webIndex/indexapi2.routes.php';

foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webIndex/indexapi2.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webIndex/indexapi2.routes.php';
	}
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/indexapi2.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/indexapi2.routes.php';
	}
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSingleSite/indexapi2.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSingleSite/indexapi2.routes.php';
	}
}


$app->run(); 


