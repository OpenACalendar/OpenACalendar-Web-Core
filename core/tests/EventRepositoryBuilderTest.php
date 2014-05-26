<?php

use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;
use repositories\builders\EventRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */




class EventRepositoryBuilderTest  extends \PHPUnit_Framework_TestCase {

	
	
	function testFilterAreaAndIncludeAreaAndIncludeVenue() {
		$DB = getNewTestDB();
		addCountriesToTestDB();
		
		$countryRepo = new CountryRepository();
		$areaRepo = new AreaRepository();
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), getSiteQuotaUsedForTesting());
		
		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");
		
		$areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);
		$areaRepo->buildCacheAreaHasParent($area);
		
		
		
		######################## For now just test it doesn't crash, I commited a bug that did crash here
		
		$erb = new EventRepositoryBuilder();
		$erb->setArea($area);
		$erb->setIncludeVenueInformation(true);
		$erb->setIncludeAreaInformation(true);
		$erb->fetchAll();
		
		
	}
	
	
}
