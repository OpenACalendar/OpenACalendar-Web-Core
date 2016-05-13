<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\SiteQuotaRepository;
use models\SiteModel;

/**
 * Creates a site.
 * 
 * This shouldn't really be here; but at the moment it's used by the install process.
 * It should be in cliapi1 and there should be a seperate explicit installer (web, cli, or both)
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$siteRepository = new SiteRepository($app);
$countryRepository = new CountryRepository($app);
$siteQuotaRepository = new SiteQuotaRepository($app);
$userRepository = new UserAccountRepository($app);



$canAnyUserVerifiedEdit = false;
$opts = getopt('',array('write'));
if (isset($opts['write'])) { $canAnyUserVerifiedEdit = true; }

$i = 1;
while(substr($argv[$i],0,1) == '-') $i++;
$slug = $argv[$i];
$email = $argv[$i+1];
if (!$slug || !$email) {
	print "Slug and Email?\n\n";
	exit(1);
}

if (!SiteModel::isSlugValid($slug, $CONFIG)) {
	print "Slug is not valid!\n\n";
	exit(1);
}

if ($siteRepository->loadBySlug(($slug))) {
	print "Slug already used!\n\n";
	exit(1);
}

print "Slug: ". $slug."\n";
print "Email: ". $email."\n";
print "Can any verified user edit: ".($canAnyUserVerifiedEdit ? "true" : "false")."\n";

sleep(10);

print "Starting ...\n";

$user = $userRepository->loadByUserNameOrEmail($email);
if (!$user) {
	die("Can't load user!\n\n");
}

$site = new SiteModel();
$site->setSlug($slug);
$site->setTitle($slug);
$site->setIsListedInIndex(true);
$site->setIsWebRobotsAllowed(true);
$site->setPromptEmailsDaysInAdvance($CONFIG->newSitePromptEmailsDaysInAdvance);


$gb = $countryRepository->loadByTwoCharCode("GB") ;
if (!$gb) {
	die("Can't load Country GB - have you loaded static data?\n\n");
}		
		
$siteRepository->create(
			$site, 
			$user, 
			array( $gb ), 
			$siteQuotaRepository->loadByCode($CONFIG->newSiteHasQuotaCode),
			$canAnyUserVerifiedEdit
		);

exit(0);



