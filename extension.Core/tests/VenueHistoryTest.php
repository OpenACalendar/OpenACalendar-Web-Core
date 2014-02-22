<?php

use models\VenueHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\VenueModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\VenueRepository;
use repositories\VenueHistoryRepository;
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
class VenueHistoryTest extends \PHPUnit_Framework_TestCase {

	
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
		
		## Create venue
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setCountryId($gb->getId());
		
		$venueRepo = new VenueRepository();
		$venueRepo->create($venue, $site, $user);
		
		## Edit venue
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);
		
		$venue = $venueRepo->loadById($venue->getId());
		$venue->setDescription("testy");
		$venue->setLat(3.6);
		$venue->setLng(3.7);
		$venueRepo->edit($venue, $user);
		
		## Now save changed flags on these .....
		$venueHistoryRepo = new VenueHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM venue_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$venueHistory = new VenueHistoryModel();
			$venueHistory->setFromDataBaseRow($data);
			$venueHistoryRepo->ensureChangedFlagsAreSet($venueHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setVenue($venue);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeGroupHistory(false);
		$historyRepo->setIncludeVenueHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getCountryIdChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
		$this->assertEquals(true, $histories[0]->getLatChanged());		
		$this->assertEquals(true, $histories[0]->getLngChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(true, $histories[1]->getCountryIdChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());
		$this->assertEquals(false, $histories[1]->getLatChanged());		
		$this->assertEquals(false, $histories[1]->getLngChanged());		

				
		
	} 
	
	function testSetChangedFlagsFromNothing1() {
		$venueHistory = new VenueHistoryModel();
		$venueHistory->setFromDataBaseRow(array(
			'venue_id'=>1,
			'title'=>'New Venue',
			'description'=>'',
			'lat'=>3.5,
			'lng'=>3.5,
			'country_id'=>88,
			'area_id'=>4,
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'area_id_changed'=>0,
			'lng_changed'=>0,
			'lat_changed'=>0,
			'area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$venueHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(true,  $venueHistory->getTitleChanged());
		$this->assertEquals(false,  $venueHistory->getDescriptionChanged());
		$this->assertEquals(true,  $venueHistory->getCountryIdChanged());
		$this->assertEquals(true,  $venueHistory->getAreaIdChanged());
		$this->assertEquals(false,  $venueHistory->getIsDeletedChanged());
		$this->assertEquals(true,  $venueHistory->getLatChanged());
		$this->assertEquals(true,  $venueHistory->getLngChanged());
	}
	
	function testSetChangedFlagsFromNothing2() {
		$venueHistory = new VenueHistoryModel();
		$venueHistory->setFromDataBaseRow(array(
			'venue_id'=>1,
			'title'=>'',
			'description'=>'CAT',
			'lat'=>null,
			'lng'=>null,
			'country_id'=>88,
			'area_id'=>4,
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'area_id_changed'=>0,
			'lng_changed'=>0,
			'lat_changed'=>0,
			'area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$venueHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(false,  $venueHistory->getTitleChanged());
		$this->assertEquals(true,  $venueHistory->getDescriptionChanged());
		$this->assertEquals(true,  $venueHistory->getCountryIdChanged());
		$this->assertEquals(true,  $venueHistory->getAreaIdChanged());
		$this->assertEquals(false,  $venueHistory->getIsDeletedChanged());
		$this->assertEquals(false,  $venueHistory->getLatChanged());
		$this->assertEquals(false ,  $venueHistory->getLngChanged());
	}
	
	
	function testSetChangedFlagsFromLast1() {
		$lastHistory = new VenueHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'venue_id'=>1,
			'title'=>'New Venue',
			'description'=>'',
			'lat'=>3.5,
			'lng'=>3.5,
			'country_id'=>88,
			'area_id'=>4,
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'area_id_changed'=>0,
			'lng_changed'=>0,
			'lat_changed'=>0,
			'area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$venueHistory = new VenueHistoryModel();
		$venueHistory->setFromDataBaseRow(array(
			'venue_id'=>1,
			'title'=>'New Venue',
			'description'=>'cat dog',
			'lat'=>4.5,
			'lng'=>3.5,
			'country_id'=>88,
			'area_id'=>4,
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'country_id_changed'=>0,
			'area_id_changed'=>0,
			'lng_changed'=>0,
			'lat_changed'=>0,
			'area_id_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$venueHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $venueHistory->getTitleChanged());
		$this->assertEquals(true,  $venueHistory->getDescriptionChanged());
		$this->assertEquals(false,  $venueHistory->getCountryIdChanged());
		$this->assertEquals(false,  $venueHistory->getIsDeletedChanged());
		$this->assertEquals(true,  $venueHistory->getLatChanged());
		$this->assertEquals(false,  $venueHistory->getLngChanged());
	}
	
	
}

