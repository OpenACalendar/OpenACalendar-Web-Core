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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$siteRepository = new SiteRepository();
$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);



header('Content-Type: application/javascript');
if (!$site) {
	// 404 TODO
	print "";
} else if ($site->getIsClosedBySysAdmin()) {
	// TODO
	print "";
} else {
	// ================ cache for a bit
	// the v and u passed to this have no effect here - they are just cache busters
	header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 30*60));

	$data  = array();
	// TODO would like to depreceate httpDomain and get scripts to just use httpDomainIndex & httpDomainSite for clarity
	$data['httpDomain'] = $site->getSlug().".".$CONFIG->webSiteDomain;
	$data['httpDomainIndex'] = $CONFIG->webIndexDomain;
	if ($CONFIG->hasSSL) {
		$data['hasSSL'] = true;
		$data['httpsDomain'] = $site->getSlug().".".$CONFIG->webSiteDomainSSL;
		$data['httpsDomainIndex'] = $CONFIG->webIndexDomainSSL;
	} else {
		$data['hasSSL'] = false;
	}
	$data['twitter'] = $CONFIG->contactTwitter;
	$data['isSingleSiteMode'] = false;
	$user = userGetCurrent();
	if ($user) {
		$data['currentUser'] = array(
			'username'=> $user->getUsername(),
		);
	} else {
		$data['currentUser'] = false;
	}
	
	print "var config = ".json_encode($data);
	
}
