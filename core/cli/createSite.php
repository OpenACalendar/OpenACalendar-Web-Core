<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\SiteQuotaRepository;
use models\SiteModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$slug = $argv[1];
$email = $argv[2];
if (!$slug || !$email) {
	die("Slug and Email?\n\n");
}

print "Slug: ". $slug."\n";
print "Email: ". $email."\n";

sleep(10);

print "Starting ...\n";

$userRepository = new UserAccountRepository();
$user = $userRepository->loadByUserNameOrEmail($email);

$site = new SiteModel();
$site->setSlug($slug);
$site->setTitle($slug);
$site->setIsListedInIndex(true);
$site->setIsWebRobotsAllowed(true);
$site->setIsAllUsersEditors(true);
$site->setIsRequestAccessAllowed(false);
$site->setIsFeatureCuratedList($CONFIG->newSiteHasFeatureCuratedList);
$site->setIsFeatureImporter($CONFIG->newSiteHasFeatureImporter);
$site->setIsFeatureMap($CONFIG->newSiteHasFeatureMap);
$site->setIsFeatureVirtualEvents($CONFIG->newSiteHasFeatureVirtualEvents);
$site->setIsFeaturePhysicalEvents($CONFIG->newSiteHasFeaturePhysicalEvents);
$site->setIsFeatureGroup($CONFIG->newSiteHasFeatureGroup);
$site->setPromptEmailsDaysInAdvance($CONFIG->newSitePromptEmailsDaysInAdvance);

$siteRepository = new SiteRepository();
$countryRepository = new CountryRepository();
$siteQuotaRepository = new SiteQuotaRepository();

$siteRepository->create(
			$site, 
			$user, 
			array( $countryRepository->loadByTwoCharCode("GB") ), 
			$siteQuotaRepository->loadByCode($CONFIG->newSiteHasQuotaCode)
		);



