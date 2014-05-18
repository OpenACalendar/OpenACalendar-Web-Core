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
	
});

$appUserRequired = function(Request $request) use ($app) {
	global $CONFIG;
	$data = array_merge($_POST, $_GET);
	
	// App?
	$appRepo = new API2ApplicationRepository();
	$apiapp = $appRepo->loadByAppToken($data['app_token']);
	if (!$apiapp) {
		// TODO also if app closed
		die("ERROR"); // TODO something better
	}
	$app['userAgent']->setApi2ApplicationId($apiapp->getId());
	
	// User Token
	$userTokenRepo = new API2ApplicationUserTokenRepository();
	$app['apiUserToken'] = $userTokenRepo->loadByAppAndUserTokenAndUserSecret($apiapp, $data['user_token'], $data['user_secret']);
	if (!$app['apiUserToken']) {
		// TODO also if user account closed
		die("ERROR"); // TODO something better
	}
	
	// User
	$userRepo = new UserAccountRepository();
	$app['apiUser'] = $userRepo->loadByID($app['apiUserToken']->getUserId());
	
	$app['apiUserIsWriteUserActions'] = false;
	$app['apiUserIsWriteUserProfile'] = FALSE;
	$app['apiUserIsWriteCalendar'] = FALSE;
	
	$uisr = new UserInSiteRepository();
	$app['currentUserInSite'] = $uisr->loadBySiteAndUserAccount($app['currentSite'], $app['apiUser'] );
	if ($app['apiUser'] ->getIsEmailVerified() && $app['apiUser'] ->getIsEditor()) {
		if ($app['currentUserInSite'] && $app['currentUserInSite']->getIsOwner()) {
			$app['apiUserIsWriteCalendar']  = $app['apiUserToken']->getIsWriteCalendar();
		} else if ($app['currentUserInSite'] && $app['currentUserInSite']->getIsAdministrator()) {
			$app['apiUserIsWriteCalendar']  = $app['apiUserToken']->getIsWriteCalendar();
		} else if ($app['currentSite']->getIsAllUsersEditors() ) {
			$app['apiUserIsWriteCalendar']  = $app['apiUserToken']->getIsWriteCalendar();
		} else if ($app['currentUserInSite'] && $app['currentUserInSite']->getIsEditor()) {
			$app['apiUserIsWriteCalendar']  = $app['apiUserToken']->getIsWriteCalendar();
		};
	}
	
};

$appVerifiedEditorUserRequired = function(Request $request)  use ($app) {
	if (!$app['apiUserIsWriteCalendar']) {
		die("ERROR"); // TODO something better
	}
};

require APP_ROOT_DIR.'/core/webSite/indexapi2.routes.php';

foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/indexapi2.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSite/indexapi2.routes.php';
	}
}


$app->run(); 


