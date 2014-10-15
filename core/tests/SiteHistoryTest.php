<?php

use models\SiteHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\SiteHistoryRepository;
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
class SiteHistoryTest extends \PHPUnit_Framework_TestCase {

	/**
	function testIntegration1() {
		 TODO
				
		
	}  **/
	
	function testSetChangedFlagsFromNothing1() {
		$siteHistory = new SiteHistoryModel();
		$siteHistory->setFromDataBaseRow(array(
			'site_id'=>1,
			'title'=>'New Site',
			'slug'=>'new_site',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
		));
		
		$siteHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(true,  $siteHistory->getTitleChanged());
		$this->assertEquals(true,  $siteHistory->getSlugChanged());
		$this->assertEquals(false,  $siteHistory->getDescriptionTextChanged());
		$this->assertEquals(false,  $siteHistory->getFooterTextChanged());
		$this->assertEquals(true,  $siteHistory->getIsWebRobotsAllowedChanged());
		$this->assertEquals(true,  $siteHistory->getIsClosedBySysAdminChanged());
		$this->assertEquals(false,  $siteHistory->getClosedBySyAdminReasonChanged());
		$this->assertEquals(true,  $siteHistory->getIsListedInIndexChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeatureMapChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeatureImporterChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeatureCuratedListChanged());
		$this->assertEquals(true,  $siteHistory->getPromptEmailsDaysInAdvanceChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeatureVirtualEventsChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeaturePhysicalEventsChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeatureGroupChanged());
		$this->assertEquals(true,  $siteHistory->getIsFeatureTagChanged());
		$this->assertEquals(true, $siteHistory->getIsNew());
	}
	
	
	function testSetChangedFlagsFromLast1() {
		$lastHistory = new SiteHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'site_id'=>1,
			'title'=>'New Site',
			'slug'=>'new_site',
			'footer_text'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
		));
		
		$siteHistory = new SiteHistoryModel();
		$siteHistory->setFromDataBaseRow(array(
			'site_id'=>1,
			'title'=>'New Site',
			'slug'=>'new_site',
			'footer_text'=>'Footer',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
		));
		
		$siteHistory->setChangedFlagsFromLast($lastHistory);
		

		$this->assertEquals(false,  $siteHistory->getTitleChanged());
		$this->assertEquals(false,  $siteHistory->getSlugChanged());
		$this->assertEquals(false,  $siteHistory->getDescriptionTextChanged());
		$this->assertEquals(true,  $siteHistory->getFooterTextChanged());
		$this->assertEquals(false,  $siteHistory->getIsWebRobotsAllowedChanged());
		$this->assertEquals(false,  $siteHistory->getIsClosedBySysAdminChanged());
		$this->assertEquals(false,  $siteHistory->getClosedBySyAdminReasonChanged());
		$this->assertEquals(false,  $siteHistory->getIsListedInIndexChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeatureMapChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeatureImporterChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeatureCuratedListChanged());
		$this->assertEquals(false,  $siteHistory->getPromptEmailsDaysInAdvanceChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeatureVirtualEventsChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeaturePhysicalEventsChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeatureGroupChanged());
		$this->assertEquals(false,  $siteHistory->getIsFeatureTagChanged());
		$this->assertEquals(false, $siteHistory->getIsNew());
	}
	
	
}

