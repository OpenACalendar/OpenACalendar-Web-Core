<?php

use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use models\CountryModelModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaCreateTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		$DB = getNewTestDB();
		addCountriesToTestDB();
		$countryRepo = new CountryRepository();
		$areaRepo = new AreaRepository();
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array( $countryRepo->loadByTwoCharCode('GB') ), getSiteQuotaUsedForTesting());

		### No areas
		$this->assertFalse($areaRepo->doesCountryHaveAnyNotDeletedAreas($site, $countryRepo->loadByTwoCharCode('GB') ));

		### Area 1
		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");
		
		$areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB') , $user);
		$areaRepo->buildCacheAreaHasParent($area);
		
		$this->checkAreaInTest1($areaRepo->loadById($area->getId()));
		$this->checkAreaInTest1($areaRepo->loadBySlug($site, $area->getSlug()));

		// no parents. Cache should be empty.
		$stat = $DB->prepare("SELECT * FROM cached_area_has_parent");
		$stat->execute();
		$this->assertEquals(0, $stat->rowCount());

		$this->assertTrue($areaRepo->doesCountryHaveAnyNotDeletedAreas($site, $countryRepo->loadByTwoCharCode('GB') ));
		
		### Area child
		$areaChild = new AreaModel();
		$areaChild->setTitle("test child");
		$areaChild->setDescription("test test child");
		
		$areaRepo->create($areaChild, $area, $site, $countryRepo->loadByTwoCharCode('GB') , $user);
		$areaRepo->buildCacheAreaHasParent($areaChild);
		
		// calling this multiple times should not crash
		$areaRepo->buildCacheAreaHasParent($areaChild);
		$areaRepo->buildCacheAreaHasParent($areaChild);
		$areaRepo->buildCacheAreaHasParent($areaChild);
		$areaRepo->buildCacheAreaHasParent($areaChild);

		$this->checkChildAreaInTest1($areaRepo->loadById($areaChild->getId()));
		$this->checkChildAreaInTest1($areaRepo->loadBySlug($site, $areaChild->getSlug()));	
		
		// Check Cache
		$stat = $DB->prepare("SELECT * FROM cached_area_has_parent WHERE area_id=".$areaChild->getId()." AND has_parent_area_id=".$area->getId());
		$stat->execute();
		$this->assertEquals(1, $stat->rowCount());

	}
	
	protected function checkAreaInTest1(AreaModel $area) {
		$this->assertEquals("test test", $area->getDescription());
		$this->assertEquals("test", $area->getTitle());
	}
	
	protected function checkChildAreaInTest1(AreaModel $area) {
		$this->assertEquals("test test child", $area->getDescription());
		$this->assertEquals("test child", $area->getTitle());
	}
}




