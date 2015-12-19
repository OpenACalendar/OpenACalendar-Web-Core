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
 
$canAnyUserVerifiedEdit = false;
$opts = getopt('',array('write'));
if (isset($opts['write'])) { $canAnyUserVerifiedEdit = true; }

$i = 1;
while(substr($argv[$i],0,1) == '-') $i++;
$slug = $argv[$i];
$email = $argv[$i+1];
if (!$slug || !$email) {
	die("Slug and Email?\n\n");
}

if (!SiteModel::isSlugValid($slug, $CONFIG)) {
	die("Slug is not valid!\n\n");
}

print "Slug: ". $slug."\n";
print "Email: ". $email."\n";
print "Can any verified user edit: ".($canAnyUserVerifiedEdit ? "true" : "false")."\n";

sleep(10);

print "Starting ...\n";

$userRepository = new UserAccountRepository();
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

$siteRepository = new SiteRepository();
$countryRepository = new CountryRepository();
$siteQuotaRepository = new SiteQuotaRepository();

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



