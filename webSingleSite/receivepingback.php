<?php
require 'localConfig.php';
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadWebApp.php';

use pingback\ParsePingBack;
use repositories\SiteRepository;

/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$siteRepository = new SiteRepository();
$site = $siteRepository->loadById($CONFIG->singleSiteID);

$data = file_get_contents('php://input');

$app['monolog']->addError("receivepingback.php got data ".$data);

$pingback = ParsePingBack::parseFromData($data);

if ($pingback) {

	$pbil = new \incominglinks\PingBackIncomingLink();
	$pbil->setSourceURL($pingback->getSourceUrl());
	$pbil->setTargetURL($pingback->getTargetUrl());
	$pbil->setReporterIp($_SERVER['REMOTE_ADDR']);
	$pbil->setReporterUseragent($_SERVER['HTTP_USER_AGENT']);

	$repo = new \repositories\IncomingLinkRepository();
	$repo->create($pbil, $site);

	print '<?xml version="1.0" encoding="ISO-8859-1"?>
<methodResponse>
   <params>
      <param>
         <value><string>Reported</string></value>
      </param>
   </params>
</methodResponse>';

} else {
	// TODO
}


