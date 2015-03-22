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

// ================ cache for a bit
// the v and u passed to this have no effect here - they are just cache busters
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 30*60));

// ================ Data!
header('Content-Type: application/javascript');
$data  = array();
// TODO would like to depreceate httpDomain and get scripts to just use httpDomainIndex & httpDomainSite for clarity
$data['httpDomain'] = $CONFIG->webIndexDomain;
$data['httpDomainIndex'] = $CONFIG->webIndexDomain;
$data['httpDomainSite'] = $CONFIG->webSiteDomain;
if ($CONFIG->hasSSL) {
	$data['hasSSL'] = true;
	$data['httpsDomain'] = $CONFIG->webIndexDomainSSL;
	$data['httpsDomainIndex'] = $CONFIG->webIndexDomainSSL;
	$data['httpsDomainSite'] = $CONFIG->webSiteDomainSSL;
} else {
	$data['hasSSL'] = false;
}
$data['isSingleSiteMode'] = $CONFIG->isSingleSiteMode;
$user = userGetCurrent();
if ($user) {
	$data['currentUser'] = array(
		'username'=> $user->getUsername(),
	);
} else {
	$data['currentUser'] = false;
}

print "var config = ".json_encode($data);
	
