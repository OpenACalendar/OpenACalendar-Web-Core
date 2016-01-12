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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

if ($app['config']->hasSSL && $app['config']->forceSSL) {
    $fr = new ForceRequestToSSL($app);
    $fr->processForIndex();
    unset($fr);
}

$app->before(function (Request $request) use ($app) {

	
	# ////////////// Timezone
	$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	$timezone = "";
	if (isset($_GET['mytimezone']) && in_array($_GET['mytimezone'], $timezones)) {
		setcookie("siteIndextimezone",$_GET['mytimezone'],time()+60*60*24*365,'/',$app['config']->webCommonSessionDomain,false,false);
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
	$app['currentUserPermissions'] = $userPermissionsRepo->getPermissionsForUserInIndex($app['currentUser'], false, true);


	$app['twig']->addGlobal('actionCreateSite', $app['currentUserPermissions']->hasPermission("org.openacalendar","CREATE_SITE"));

});


$permissionCreateSiteRequired = function(Request $request, Application $app) {
	if (!$app['currentUserPermissions']->hasPermission("org.openacalendar","CREATE_SITE")) {
		return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
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
		return new RedirectResponse('/me/verifyneeded');
	}
};

$appVerifiedEditorUserRequired = function(Request $request) use ($app) {
	if (!$app['currentUser']) {
		return new RedirectResponse($app['config']->getWebIndexDomainSecure().'/you/login');
	}
	if (!$app['currentUser']->getIsEmailVerified()) {
		return new RedirectResponse('/me/verifyneeded');
	}
	if (!$app['currentUser']->getIsEditor()) {
		die("NO"); // TODO
	}
};

$canChangeSite = function(Request $request) use ($app) {
	if ($app['config']->siteReadOnly) {
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


