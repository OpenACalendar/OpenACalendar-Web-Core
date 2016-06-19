<?php
require 'localConfig.php';
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadWebApp.php';

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

$siteRepository = new SiteRepository($app);
$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);
if (!$site) {
    die ("404 Not Found"); // TODO
}

if ($site->getIsClosedBySysAdmin()) {
    die("404 Not Found"); // TODO
}

$manifest = new ManifestSite($app);
$data = $manifest->get($site);
header('Content-Type:  application/manifest+json');
print json_encode($data);

