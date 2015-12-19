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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


///////////////////////////////////////////// SECURITY

if (!$app['currentUser']) {
	die("No");
}	
if (!$app['currentUser']->getIsSystemAdmin()) {
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

require APP_ROOT_DIR.'/core/webSysAdmin/index.routes.php';


foreach($CONFIG->extensions as $extensionName) {
	if (file_exists(APP_ROOT_DIR.'/extension/'.$extensionName.'/webSysAdmin/index.routes.php')) {
		require APP_ROOT_DIR.'/extension/'.$extensionName.'/webSysAdmin/index.routes.php';
	}
}

$app->run(); 


