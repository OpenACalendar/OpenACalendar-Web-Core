<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;

/**
 *
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


///////////////////////////////////////////// SECURITY

if (!userGetCurrent()) {
	die("No");
}	
if (!userGetCurrent()->getIsSystemAdmin()) {
	die("No");
}
if (!$WEBSESSION->has('sysAdminLastActive')) {
	header("Location: /authintosysadmin.php");
	die();
}
if ($WEBSESSION->get('sysAdminLastActive') + $CONFIG->sysAdminLogInTimeOutSeconds < TimeSource::time()) {
	header("Location: /authintosysadmin.php");
	die();
}
$WEBSESSION->set('sysAdminLastActive',\TimeSource::time());

///////////////////////////////////////////// APP


$app->before(function (Request $request) use ($app) {
	global $CONFIG;
	
	# ////////////// Timezone
	$timezone = $CONFIG->sysAdminTimeZone;
	$app['twig']->addGlobal('currentTimeZone', $timezone);	
	$app['currentTimeZone'] = $timezone;
	
});



$sysadminMenus = array();
require APP_ROOT_DIR.'/extension.Core/webSysAdmin/index.routes.php';


foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension.'.$extensionName.'/webSysAdmin/index.routes.php')) {
		require APP_ROOT_DIR.'/extension.'.$extensionName.'/webSysAdmin/index.routes.php';
	}
}

$app->before(function (Request $request) use ($app) {
	global $sysadminMenus;
	$app['twig']->addGlobal('sysadminMenus', $sysadminMenus);	
});

$app->run(); 


