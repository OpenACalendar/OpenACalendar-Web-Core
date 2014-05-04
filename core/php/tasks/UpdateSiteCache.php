<?php

namespace tasks;


use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\CountryRepositoryBuilder;
use repositories\SiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateSiteCache {

	public static function update($verbose = false) {

		if ($verbose) print "Starting ".date("c")."\n";

		$siteRepository = new SiteRepository();

		$siteRepositoryBuilder = new SiteRepositoryBuilder();
		foreach($siteRepositoryBuilder->fetchAll() as $site) {

			if ($verbose) print $site->getId().": ".$site->getTitle()."\n";

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

			if ($verbose) print "Done\n";
		}


		if ($verbose) print "Finished ".date("c")."\n";


		
		
	}

	
}

