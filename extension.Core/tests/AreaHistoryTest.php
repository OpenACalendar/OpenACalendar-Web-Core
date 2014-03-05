<?php

use models\AreaHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\AreaRepository;
use repositories\AreaHistoryRepository;
use repositories\CountryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaHistoryTest extends \PHPUnit_Framework_TestCase {

	/**
	function testIntegration1() {
		$DB = getNewTestDB();
		addCountriesToTestDB();
		\TimeSource::mock(2014, 1, 1, 12, 0, 0);
		
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
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		$countryRepo = new CountryRepository();
		$gb = $countryRepo->loadByTwoCharCode('GB');
		
		## Create area
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");
		$area->setCountryId($gb->getId());
		
		$areaRepo = new AreaRepository();
		$areaRepo->create($area, null, $site, $gb, $user);
		
		## Edit area
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);
		
		$area = $areaRepo->loadById($area->getId());
		$area->setDescription("testy");
		$areaRepo->edit($area, $user);
		
		## Now save changed flags on these .....
		$areaHistoryRepo = new AreaHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM area_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$areaHistory = new AreaHistoryModel();
			$areaHistory->setFromDataBaseRow($data);
			$areaHistoryRepo->ensureChangedFlagsAreSet($areaHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setArea($area);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(false);
		$historyRepo->setIncludeAreaHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getCountryIdChanged());
		$this->assertEquals(false, $histories[0]->getParentAreaIdChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(true, $histories[1]->getCountryIdChanged());
		$this->assertEquals(false, $histories[1]->getParentAreaIdChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());

				
		
	}  **/
	
	function testSetChangedFlagsFromNothing1() {
		$areaHistory = new AreaHistoryModel();
		$areaHistory->setFromDataBaseRow(array(
			'area_id'=>1,
			'title'=>'New Area',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'parent_area_id'=>'',
			'country_id'=>77,
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'parent_area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$areaHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(true,  $areaHistory->getTitleChanged());
		$this->assertEquals(false,  $areaHistory->getDescriptionChanged());
		$this->assertEquals(true,  $areaHistory->getCountryIdChanged());
		$this->assertEquals(false,  $areaHistory->getParentAreaIdChanged());
		$this->assertEquals(false,  $areaHistory->getIsDeletedChanged());
		$this->assertEquals(true, $areaHistory->getIsNew());
	}
	
	
	function testSetChangedFlagsFromLast1() {
		$lastHistory = new AreaHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'area_id'=>1,
			'title'=>'Cat',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'parent_area_id'=>'',
			'country_id'=>77,
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'parent_area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$areaHistory = new AreaHistoryModel();
		$areaHistory->setFromDataBaseRow(array(
			'area_id'=>1,
			'title'=>'Cat',
			'description'=>'This area has no name',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 12:00:00',
			'parent_area_id'=>6,
			'country_id'=>78,
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'parent_area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$areaHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $areaHistory->getTitleChanged());
		$this->assertEquals(true,  $areaHistory->getDescriptionChanged());
		$this->assertEquals(true,  $areaHistory->getCountryIdChanged());
		$this->assertEquals(true,  $areaHistory->getParentAreaIdChanged());
		$this->assertEquals(false,  $areaHistory->getIsDeletedChanged());
		$this->assertEquals(false, $areaHistory->getIsNew());
	}
	
	
}

