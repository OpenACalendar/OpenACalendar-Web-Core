<?php
require 'localConfig.php';
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadWebApp.php';

use repositories\SiteRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */

$siteRepository = new SiteRepository();
$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);
if (!$site) {
	die ("404 Not Found"); // TODO
}

header("HTTP/1.0 404 Not Found");
print $app['twig']->render('site/error404.html.twig', array('currentSite'=>$site));


