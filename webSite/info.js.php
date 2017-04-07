<?php
require 'localConfig.php';
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadWebApp.php';

use repositories\SiteRepository;
use repositories\UserHasNoEditorPermissionsInSiteRepository;

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
	$data['installTitle'] = $CONFIG->installTitle;
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
    $data['isWebRobotsAllowed'] = $site->getIsWebRobotsAllowed();
	$data['twitter'] = $CONFIG->contactTwitter;
	$data['isSingleSiteMode'] = false;
    // Several other keys should really be in currentSite, but for historical reasons they are outside it
    $data['currentSite'] = array(
        'isMultipleCountries' => $site->getCachedIsMultipleCountries(),
        'isMultipleTimezones' => $site->getCachedIsMultipleTimezones(),
        'id' => $site->getId(), // This is used in the name of the cookie that holds the timezone, so it is needed.
    );
	$user = userGetCurrent();
	if ($user) {
		$data['currentUser'] = array(
			'username'=> $user->getUsername(),
		);
	} else {
		$data['currentUser'] = false;
	}

	$removeEditorPermissions = false;
	$userHasNoEditorPermissionsInSiteRepo = new UserHasNoEditorPermissionsInSiteRepository($app);
	if ($app['currentUser'] && $userHasNoEditorPermissionsInSiteRepo->isUserInSite($app['currentUser'], $site)) {
		$removeEditorPermissions = true;
	}

	$userPermissionsRepo = new \repositories\UserPermissionsRepository($app);
	$currentUserPermissions = $userPermissionsRepo->getPermissionsForUserInSite($user, $site, $removeEditorPermissions, true);
	$data['currentUserPermissions'] = $currentUserPermissions->getAsArrayForJSON();

	print "var config = ".json_encode($data);
	
}
