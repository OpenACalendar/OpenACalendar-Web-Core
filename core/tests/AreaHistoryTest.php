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
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaHistoryTest extends \BaseAppTest {


	
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

