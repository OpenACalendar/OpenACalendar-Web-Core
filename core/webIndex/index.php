<?php


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
	global $CONFIG;
	
	
	# ////////////// Timezone
	$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	$timezone = "";
	if (isset($_GET['mytimezone']) && in_array($_GET['mytimezone'], $timezones)) {
		setcookie("siteIndextimezone",$_GET['mytimezone'],time()+60*60*24*365,'/',$CONFIG->webCommonSessionDomain,false,false);
		$timezone = $_GET['mytimezone'];
	} else if (isset($_COOKIE["siteIndextimezone"]) && in_array($_COOKIE["siteIndextimezone"],$timezones)) {
		$timezone = $_COOKIE["siteIndextimezone"];
	} else {
		$timezone = 'Europe/London';
	}
	$app['twig']->addGlobal('currentTimeZone', $timezone);	
	$app['twig']->addGlobal('allowedTimeZones', $timezones);	
	$app['currentTimeZone'] = $timezone;

	# ////////////// Permissions
	$userPermissionsRepo = new \repositories\UserPermissionsRepository($app['extensions']);
	$app['currentUserPermissions'] = $userPermissionsRepo->getPermissionsForUserInIndex(userGetCurrent(), false, true);


	$app['twig']->addGlobal('actionCreateSite', $app['currentUserPermissions']->hasPermission("org.openacalendar","CREATE_SITE"));

});


$permissionCreateSiteRequired = function(Request $request, Application $app) {
	global $CONFIG;
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CREATE_SITE")) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
};

$appUserRequired = function(Request $request) {
	global $CONFIG;
	if (!userGetCurrent()) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
};

$appUnverifiedUserRequired = function(Request $request) {
	global $CONFIG;
	if (!userGetCurrent()) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
	if (userGetCurrent()->getIsEmailVerified()) {
		return new RedirectResponse('/');
	}
};

$appVerifiedUserRequired = function(Request $request) {
	global $CONFIG;
	if (!userGetCurrent()) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
	if (!userGetCurrent()->getIsEmailVerified()) {
		return new RedirectResponse('/me/verifyneeded');
	}
};

$appVerifiedEditorUserRequired = function(Request $request) {
	global $CONFIG;
	if (!userGetCurrent()) {
		return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
	}
	if (!userGetCurrent()->getIsEmailVerified()) {
		return new RedirectResponse('/me/verifyneeded');
	}
	if (!userGetCurrent()->getIsEditor()) {
		die("NO"); // TODO
	}
};

$canChangeSite = function(Request $request) use ($app) {
	global  $CONFIG;
	if ($CONFIG->siteReadOnly) {
		return new Response($app['twig']->render('index/readonly.html.twig', array()));
	}		
};


$app->match('/', "index\controllers\IndexController::index");

require APP_ROOT_DIR.'/core/webIndex/index.routes.php';
require APP_ROOT_DIR.'/core/webIndex/index.routes.multisiteonly.php';

if (!$CONFIG->isDebug) {
	$app->error(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $code) use ($app) {
		if ($e->getStatusCode() == 404) {
			return new Response($app['twig']->render('index/error404.html.twig', array('exception'=>$e)));
		} else {
			return new Response($app['twig']->render('index/error.html.twig', array('exception'=>$e)));
		}
	});
}

foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webIndex/index.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webIndex/index.routes.php';
	}
}


$app->run(); 


