<?php

use models\EventHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\EventHistoryRepository;
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
class EventHistoryTest extends \PHPUnit_Framework_TestCase {

	
	function testSetChangedFlagsFromNothing1() {
		$eventHistory = new EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
				'event_id'=>1,
				'summary'=>'New Event',
				'description'=>'',
				'user_account_id'=>1,
				'created_at'=>'2014-02-01 10:00:00',
				'start_at'=>'2014-02-03 10:00:00',
				'end_at'=>'2014-02-03 15:00:00',
				'is_deleted'=>0,
				'is_cancelled'=>0,
				'country_id'=>33,
				'timezone'=>'Europe/London',
				'venue_id'=>45,
				'url'=>'',
				'ticket_url'=>'',
				'is_virtual'=>0,
				'is_physical'=>0,
				'area_id'=>'',
				'summary_changed'=>0,
				'description_changed'=>0,
				'start_at_changed'=>0,
				'end_at_changed'=>0,
				'is_deleted_changed'=>0,
				'country_id_changed'=>0,
				'timezone_changed'=>0,
				'venue_id_changed'=>0,
				'url_changed'=>0,
				'is_virtual_changed'=>0,
				'is_physical_changed'=>0,
				'area_id_changed'=>0,
			));
		
		$eventHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(true,  $eventHistory->getSummaryChanged());
		$this->assertEquals(false,  $eventHistory->getDescriptionChanged());
		$this->assertEquals(true,  $eventHistory->getStartAtChanged());
		$this->assertEquals(true,  $eventHistory->getEndAtChanged());
		$this->assertEquals(false,  $eventHistory->getIsDeletedChanged());
		$this->assertEquals(false,  $eventHistory->getIsCancelledChanged());
		$this->assertEquals(true,  $eventHistory->getCountryIdChanged());
		$this->assertEquals(true,  $eventHistory->getTimezoneChanged());
		$this->assertEquals(true,  $eventHistory->getVenueIdChanged());
		$this->assertEquals(false,  $eventHistory->getUrlChanged());
		$this->assertEquals(false,  $eventHistory->getTicketUrlChanged());
		$this->assertEquals(true,  $eventHistory->getIsVirtualChanged());
		$this->assertEquals(true,  $eventHistory->getIsPhysicalChanged());
		$this->assertEquals(false,  $eventHistory->getAreaIdChanged());
		$this->assertEquals(true, $eventHistory->getIsNew());
	}
	
	
	function testSetChangedFlagsFromLast1() {
		$lastHistory = new EventHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
				'event_id'=>1,
				'summary'=>'New Event',
				'description'=>'',
				'user_account_id'=>1,
				'created_at'=>'2014-02-01 10:00:00',
				'start_at'=>'2014-02-03 10:00:00',
				'end_at'=>'2014-02-03 15:00:00',
				'is_deleted'=>0,
				'is_cancelled'=>0,
				'country_id'=>'',
				'timezone'=>'',
				'venue_id'=>45,
				'url'=>'',
				'ticket_url'=>'',
				'is_virtual'=>0,
				'is_physical'=>0,
				'area_id'=>'',
				'summary_changed'=>0,
				'description_changed'=>0,
				'start_at_changed'=>0,
				'end_at_changed'=>0,
				'is_deleted_changed'=>0,
				'country_id_changed'=>0,
				'timezone_changed'=>0,
				'venue_id_changed'=>0,
				'url_changed'=>0,
				'is_virtual_changed'=>0,
				'is_physical_changed'=>0,
				'area_id_changed'=>0,		));
		
		$eventHistory = new EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
				'event_id'=>1,
				'summary'=>'New Event',
				'description'=>'A good event',
				'user_account_id'=>1,
				'created_at'=>'2014-02-01 13:00:00',
				'start_at'=>'2014-02-03 10:00:00',
				'end_at'=>'2014-02-03 15:00:00',
				'is_deleted'=>0,
				'is_cancelled'=>0,
				'country_id'=>'',
				'timezone'=>'',
				'venue_id'=>null,
				'url'=>'',
				'ticket_url'=>'',
				'is_virtual'=>0,
				'is_physical'=>0,
				'area_id'=>56,
				'summary_changed'=>0,
				'description_changed'=>0,
				'start_at_changed'=>0,
				'end_at_changed'=>0,
				'is_deleted_changed'=>0,
				'country_id_changed'=>0,
				'timezone_changed'=>0,
				'venue_id_changed'=>0,
				'url_changed'=>0,
				'is_virtual_changed'=>0,
				'is_physical_changed'=>0,
				'area_id_changed'=>0,		));
		
		$eventHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $eventHistory->getSummaryChanged());
		$this->assertEquals(true,  $eventHistory->getDescriptionChanged());
		$this->assertEquals(false,  $eventHistory->getStartAtChanged());
		$this->assertEquals(false,  $eventHistory->getEndAtChanged());
		$this->assertEquals(false,  $eventHistory->getIsDeletedChanged());
		$this->assertEquals(false,  $eventHistory->getIsCancelledChanged());
		$this->assertEquals(false,  $eventHistory->getCountryIdChanged());
		$this->assertEquals(false,  $eventHistory->getTimezoneChanged());
		$this->assertEquals(true,  $eventHistory->getVenueIdChanged());
		$this->assertEquals(false,  $eventHistory->getUrlChanged());
		$this->assertEquals(false,  $eventHistory->getTicketUrlChanged());
		$this->assertEquals(false,  $eventHistory->getIsVirtualChanged());
		$this->assertEquals(false,  $eventHistory->getIsPhysicalChanged());
		$this->assertEquals(true,  $eventHistory->getAreaIdChanged());	
		$this->assertEquals(false, $eventHistory->getIsNew());
	}
	


	function testSetChangedFlagsFromLastDelete() {
		$lastHistory = new EventHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
				'event_id'=>1,
				'summary'=>'New Event',
				'description'=>'',
				'user_account_id'=>1,
				'created_at'=>'2014-02-01 10:00:00',
				'start_at'=>'2014-02-03 10:00:00',
				'end_at'=>'2014-02-03 15:00:00',
				'is_deleted'=>0,
				'is_cancelled'=>0,
				'country_id'=>'',
				'timezone'=>'',
				'venue_id'=>45,
				'url'=>'',
				'ticket_url'=>'',
				'is_virtual'=>0,
				'is_physical'=>0,
				'area_id'=>'',
				'summary_changed'=>0,
				'description_changed'=>0,
				'start_at_changed'=>0,
				'end_at_changed'=>0,
				'is_deleted_changed'=>0,
				'country_id_changed'=>0,
				'timezone_changed'=>0,
				'venue_id_changed'=>0,
				'url_changed'=>0,
				'is_virtual_changed'=>0,
				'is_physical_changed'=>0,
				'area_id_changed'=>0,		));

		$eventHistory = new EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
			'event_id'=>1,
			'summary'=>'New Event',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'start_at'=>'2014-02-03 10:00:00',
			'end_at'=>'2014-02-03 15:00:00',
			'is_deleted'=>1,
			'is_cancelled'=>0,
			'country_id'=>'',
			'timezone'=>'',
			'venue_id'=>45,
			'url'=>'',
			'ticket_url'=>'',
			'is_virtual'=>0,
			'is_physical'=>0,
			'area_id'=>'',
			'summary_changed'=>0,
			'description_changed'=>0,
			'start_at_changed'=>0,
			'end_at_changed'=>0,
			'is_deleted_changed'=>0,
			'country_id_changed'=>0,
			'timezone_changed'=>0,
			'venue_id_changed'=>0,
			'url_changed'=>0,
			'is_virtual_changed'=>0,
			'is_physical_changed'=>0,
			'area_id_changed'=>0,		));

		$eventHistory->setChangedFlagsFromLast($lastHistory);

		$this->assertEquals(false,  $eventHistory->getSummaryChanged());
		$this->assertEquals(false,  $eventHistory->getDescriptionChanged());
		$this->assertEquals(false,  $eventHistory->getStartAtChanged());
		$this->assertEquals(false,  $eventHistory->getEndAtChanged());
		$this->assertEquals(true,  $eventHistory->getIsDeletedChanged());
		$this->assertEquals(false,  $eventHistory->getIsCancelledChanged());
		$this->assertEquals(false,  $eventHistory->getCountryIdChanged());
		$this->assertEquals(false,  $eventHistory->getTimezoneChanged());
		$this->assertEquals(false,  $eventHistory->getVenueIdChanged());
		$this->assertEquals(false,  $eventHistory->getUrlChanged());
		$this->assertEquals(false,  $eventHistory->getTicketUrlChanged());
		$this->assertEquals(false,  $eventHistory->getIsVirtualChanged());
		$this->assertEquals(false,  $eventHistory->getIsPhysicalChanged());
		$this->assertEquals(false,  $eventHistory->getAreaIdChanged());
		$this->assertEquals(false, $eventHistory->getIsNew());
	}


	function testSetChangedFlagsFromLastCancel() {
		$lastHistory = new EventHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
				'event_id'=>1,
				'summary'=>'New Event',
				'description'=>'',
				'user_account_id'=>1,
				'created_at'=>'2014-02-01 10:00:00',
				'start_at'=>'2014-02-03 10:00:00',
				'end_at'=>'2014-02-03 15:00:00',
				'is_deleted'=>0,
				'is_cancelled'=>0,
				'country_id'=>'',
				'timezone'=>'',
				'venue_id'=>45,
				'url'=>'',
				'ticket_url'=>'',
				'is_virtual'=>0,
				'is_physical'=>0,
				'area_id'=>'',
				'summary_changed'=>0,
				'description_changed'=>0,
				'start_at_changed'=>0,
				'end_at_changed'=>0,
				'is_deleted_changed'=>0,
				'country_id_changed'=>0,
				'timezone_changed'=>0,
				'venue_id_changed'=>0,
				'url_changed'=>0,
				'is_virtual_changed'=>0,
				'is_physical_changed'=>0,
				'area_id_changed'=>0,		));

		$eventHistory = new EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
			'event_id'=>1,
			'summary'=>'New Event',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'start_at'=>'2014-02-03 10:00:00',
			'end_at'=>'2014-02-03 15:00:00',
			'is_deleted'=>0,
			'is_cancelled'=>1,
			'country_id'=>'',
			'timezone'=>'',
			'venue_id'=>45,
			'url'=>'',
			'ticket_url'=>'',
			'is_virtual'=>0,
			'is_physical'=>0,
			'area_id'=>'',
			'summary_changed'=>0,
			'description_changed'=>0,
			'start_at_changed'=>0,
			'end_at_changed'=>0,
			'is_deleted_changed'=>0,
			'country_id_changed'=>0,
			'timezone_changed'=>0,
			'venue_id_changed'=>0,
			'url_changed'=>0,
			'is_virtual_changed'=>0,
			'is_physical_changed'=>0,
			'area_id_changed'=>0,		));

		$eventHistory->setChangedFlagsFromLast($lastHistory);

		$this->assertEquals(false,  $eventHistory->getSummaryChanged());
		$this->assertEquals(false,  $eventHistory->getDescriptionChanged());
		$this->assertEquals(false,  $eventHistory->getStartAtChanged());
		$this->assertEquals(false,  $eventHistory->getEndAtChanged());
		$this->assertEquals(false,  $eventHistory->getIsDeletedChanged());
		$this->assertEquals(true,  $eventHistory->getIsCancelledChanged());
		$this->assertEquals(false,  $eventHistory->getCountryIdChanged());
		$this->assertEquals(false,  $eventHistory->getTimezoneChanged());
		$this->assertEquals(false,  $eventHistory->getVenueIdChanged());
		$this->assertEquals(false,  $eventHistory->getUrlChanged());
		$this->assertEquals(false,  $eventHistory->getTicketUrlChanged());
		$this->assertEquals(false,  $eventHistory->getIsVirtualChanged());
		$this->assertEquals(false,  $eventHistory->getIsPhysicalChanged());
		$this->assertEquals(false,  $eventHistory->getAreaIdChanged());
		$this->assertEquals(false, $eventHistory->getIsNew());
	}


}

