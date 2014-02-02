<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\CountryRepositoryBuilder;
use repositories\SiteRepository;

$siteRepository = new SiteRepository();

$siteRepositoryBuilder = new SiteRepositoryBuilder();
foreach($siteRepositoryBuilder->fetchAll() as $site) {

	print $site->getId().": ".$site->getTitle()."\n";
	
	$crb = new CountryRepositoryBuilder();
	$crb->setSiteIn($site);
	$countries = $crb->fetchAll();
	
	$timezones = array();
	foreach($countries as $country) {
		foreach(explode(",", $country->getTimezones()) as $timeZone) {
			$timezones[] = $timeZone;
		}
	}
	
	$site->setCachedTimezonesAsList($timezones);
	$site->setCachedIsMultipleCountries(count($countries) > 1);
	
	$siteRepository->editCached($site);
	
	print "Done\n";
}


