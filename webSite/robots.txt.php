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
} else {
    // There is a valid site.
    // Allow all access. Meta tags sort out indexing.
    print "";
}

