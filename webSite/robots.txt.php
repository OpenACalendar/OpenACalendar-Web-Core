<?php
require 'localConfig.php';
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadWebApp.php';

use repositories\SiteRepository;

/**
 *
 * This is seperate from the index.php handler.
 * There we want a blanket rule that if the site is closed by admin we dump HTML and die()
 * Here,  if the site is closed by admin we want to print a valid "DENY ROBOTS" file.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$siteRepository = new SiteRepository($app);
$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);

if (!$site) {
	// 404 - deny for now
	print "User-agent: *\nDisallow: /";
} else if ($site && $site->getIsWebRobotsAllowed()  && !$site->getIsClosedBySysAdmin()) {
	// ALLOW
	print "User-agent: *\n".
			"Disallow: /mytimezone\n".
			"Disallow: /history\n".
			"Disallow: /currentuser\n".
			"Disallow: /demo\n".
			"Disallow: /leaflet-0-7-1/\n".
			"Disallow: /displayboard/run/\n";
} else {
	// DENY
	// we can't just do Disallow / because then Google Calendar which fetches feeds with Googlebot goes "EWWWW" and refuses to work.
	// so we have to deny the real pages one by one
	print "User-agent: *\n".
			"Disallow: /curatedlist\n".
			"Disallow: /event\n".
			"Disallow: /group\n".
			"Disallow: /venue\n".
			"Disallow: /map\n".
			"Disallow: /country\n".
			"Disallow: /history\n".
			"Disallow: /mytimezone\n".
			"Disallow: /currentuser\n".
			"Disallow: /places\n".
			"Disallow: /demo\n".
			"Disallow: /leaflet-0-7-1/\n".
			"Disallow: /displayboard/run/\n".
			"";
}

