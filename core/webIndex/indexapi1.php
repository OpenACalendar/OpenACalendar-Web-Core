<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;




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
});

require APP_ROOT_DIR.'/core/webIndex/indexapi1.routes.php';


foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension.'.$extensionName.'/webIndex/indexapi1.routes.php')) {
		require APP_ROOT_DIR.'/extension.'.$extensionName.'/webIndex/indexapi1.routes.php';
	}
}


$app->run(); 
