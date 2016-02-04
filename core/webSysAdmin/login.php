<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;



/**
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


///////////////////////////////////////////// APP

$app->before(function (Request $request) use ($app) {
	# ////////////// Timezone
	$timezone = $app['config']->sysAdminTimeZone;
	$app['twig']->addGlobal('currentTimeZone', $timezone);	
	$app['currentTimeZone'] = $timezone;

});


$app->match('/', "sysadmin\controllers\LogInController::index"); 
$app->run(); 

