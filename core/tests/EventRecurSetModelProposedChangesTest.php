<?php

use models\EventModel;
use models\EventRecurSetModel;
use models\EventHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelProposedChangesTest extends \PHPUnit_Framework_TestCase {
	
	/*
	 */
	function testChangeDescription1() {
		
		TimeSource::mock(2014,1,1,9,0,0);
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$initialEvent = new EventModel();
		$initialEvent->setSlug(1);
		$initialEvent->setDescription("Dogs go miaow"); // this is the inital wrong value
		$initialEvent->setStartAt(getUTCDateTime(2014,1,7,19,0,0));
		$initialEvent->setEndAt(getUTCDateTime(2014,1,7,21,0,0));		
		$eventSet->setInitialEventJustBeforeLastChange($initialEvent);
		
		$initialEventEdited = clone $initialEvent;
		$initialEventEdited->setDescription("Dogs go woof"); // this is the later corrected value
		$eventSet->setInitalEvent($initialEventEdited);
		
		$futureEvent1 = new EventModel();
		$futureEvent1->setSlug(2);
		$futureEvent1->setDescription("Dogs go miaow"); // this is the earlier wrong value that we will want to overwrite
		$futureEvent1->setStartAt(getUTCDateTime(2014,1,14,19,0,0));
		$futureEvent1->setEndAt(getUTCDateTime(2014,1,14,21,0,0));		
		
		$futureEvent2 = new EventModel();
		$futureEvent2->setSlug(3);
		$futureEvent2->setDescription("Dogs go woof"); // this value has already been corrected
		$futureEvent2->setStartAt(getUTCDateTime(2014,1,21,19,0,0));
		$futureEvent2->setEndAt(getUTCDateTime(2014,1,21,21,0,0));		
		
		$futureEvent3 = new EventModel();
		$futureEvent3->setSlug(4);
		$futureEvent3->setDescription("Dogs go miaowwwwwwww"); // this value has already been edited. Possible to overwrite but not recommended
		$futureEvent3->setStartAt(getUTCDateTime(2014,1,21,19,0,0));
		$futureEvent3->setEndAt(getUTCDateTime(2014,1,21,21,0,0));		
		
		$eventSet->setFutureEvents( array( $futureEvent1, $futureEvent2, $futureEvent3 ));
		
		$eventHistory = new \models\EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
			'event_id'=>null,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>null,
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'-1',
			'description_changed'=>'1',
			'start_at_changed'=>'-1',
			'end_at_changed'=>'-1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
		));
		$eventSet->setInitalEventLastChange($eventHistory);
		
		## Detect !!!
		$eventSet->applyChangeToFutureEvents();
		
		## Test Changes Picked Up
		$proposedChanges1 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent1->getSlug());
		$this->assertEquals(false, $proposedChanges1->getSummaryChangePossible());
		$this->assertEquals(false, $proposedChanges1->getSummaryChangeSelected());
		$this->assertEquals(true, $proposedChanges1->getDescriptionChangePossible());
		$this->assertEquals(true, $proposedChanges1->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges1->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges1->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges1->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges1->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges1->getIsPhysicalChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges1->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges1->isAnyChangesPossible());
		
		$proposedChanges2 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent2->getSlug());
		$this->assertEquals(false, $proposedChanges2->getSummaryChangePossible());
		$this->assertEquals(false, $proposedChanges2->getSummaryChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getDescriptionChangePossible());
		$this->assertEquals(false, $proposedChanges2->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges2->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges2->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges2->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges2->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges2->getIsPhysicalChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges2->getStartEndAtChangeSelected());
		$this->assertEquals(false, $proposedChanges2->isAnyChangesPossible());
		
		$proposedChanges3 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent3->getSlug());
		$this->assertEquals(false, $proposedChanges3->getSummaryChangePossible());
		$this->assertEquals(false, $proposedChanges3->getSummaryChangeSelected());
		$this->assertEquals(true, $proposedChanges3->getDescriptionChangePossible());
		$this->assertEquals(false, $proposedChanges3->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges3->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges3->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges3->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges3->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges3->getIsPhysicalChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges3->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges3->isAnyChangesPossible());
		
		
		## Now Set to don't update and run and test
		$proposedChanges1->setDescriptionChangeSelected(false);
		$this->assertEquals(false, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals("Dogs go miaow", $futureEvent1->getDescription());
		
		
		## Now Set to do update and run and test
		$proposedChanges1->setDescriptionChangeSelected(true);
		$this->assertEquals(true, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals("Dogs go woof", $futureEvent1->getDescription());
		
	}
	
	
	
	
	function dataForTestChangeSummary1() {
		return array(
				array('Jan','Feb','Mar','Apr'),
				array('January','February','March','April'),
			);
	}
	
	/**
     * @dataProvider dataForTestChangeSummary1
     */	
	function testChangeSummary1($janMonthName, $febMonthName, $marMonthName, $aprMonthName) {
		
		TimeSource::mock(2014,1,1,9,0,0);
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$initialEvent = new EventModel();
		$initialEvent->setSlug(1);
		$initialEvent->setSummary("Party in ".$janMonthName);
		$initialEvent->setStartAt(getUTCDateTime(2014,1,7,19,0,0));
		$initialEvent->setEndAt(getUTCDateTime(2014,1,7,21,0,0));		
		$eventSet->setInitialEventJustBeforeLastChange($initialEvent);
		
		$initialEventEdited = clone $initialEvent;
		$initialEventEdited->setSummary("Party like an iguana in ".$janMonthName);
		$eventSet->setInitalEvent($initialEventEdited);
		
		$futureEvent1 = new EventModel();
		$futureEvent1->setSlug(2);
		$futureEvent1->setSummary("Party in ".$febMonthName); 
		$futureEvent1->setStartAt(getUTCDateTime(2014,2,14,19,0,0));
		$futureEvent1->setEndAt(getUTCDateTime(2014,2,14,21,0,0));		
		
		$futureEvent2 = new EventModel();
		$futureEvent2->setSlug(3);
		$futureEvent2->setSummary("Party in ".$marMonthName);
		$futureEvent2->setStartAt(getUTCDateTime(2014,3,21,19,0,0));
		$futureEvent2->setEndAt(getUTCDateTime(2014,3,21,21,0,0));		
		
		$futureEvent3 = new EventModel();
		$futureEvent3->setSlug(4);
		$futureEvent3->setSummary("Party in ".$aprMonthName); 
		$futureEvent3->setStartAt(getUTCDateTime(2014,4,21,19,0,0));
		$futureEvent3->setEndAt(getUTCDateTime(2014,4,21,21,0,0));		
		
		$eventSet->setFutureEvents( array( $futureEvent1, $futureEvent2, $futureEvent3 ));
		
		$eventHistory = new \models\EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
			'event_id'=>null,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>null,
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'1',
			'description_changed'=>'-1',
			'start_at_changed'=>'-1',
			'end_at_changed'=>'-1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
		));
		$eventSet->setInitalEventLastChange($eventHistory);
		
		## Detect !!!
		$eventSet->applyChangeToFutureEvents();
		
		## Test Changes Picked Up
		$proposedChanges1 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent1->getSlug());
		$this->assertEquals(true, $proposedChanges1->getSummaryChangePossible());
		$this->assertEquals(true, $proposedChanges1->getSummaryChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getDescriptionChangePossible());
		$this->assertEquals(false, $proposedChanges1->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges1->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges1->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges1->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges1->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges1->getIsPhysicalChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges1->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges1->isAnyChangesPossible());
		
		$proposedChanges2 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent2->getSlug());
		$this->assertEquals(true, $proposedChanges2->getSummaryChangePossible());
		$this->assertEquals(true, $proposedChanges2->getSummaryChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getDescriptionChangePossible());
		$this->assertEquals(false, $proposedChanges2->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges2->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges2->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges2->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges2->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges2->getIsPhysicalChangeSelected());
		$this->assertEquals(false, $proposedChanges2->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges2->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges2->isAnyChangesPossible());
		
		$proposedChanges3 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent3->getSlug());
		$this->assertEquals(true, $proposedChanges3->getSummaryChangePossible());
		$this->assertEquals(true, $proposedChanges3->getSummaryChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getDescriptionChangePossible());
		$this->assertEquals(false, $proposedChanges3->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges3->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges3->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges3->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges3->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges3->getIsPhysicalChangeSelected());
		$this->assertEquals(false, $proposedChanges3->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges3->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges3->isAnyChangesPossible());
		
		
		## Now Set to don't update and run and test
		$proposedChanges1->setSummaryChangeSelected(false);
		$this->assertEquals(false, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals("Party in ".$febMonthName, $futureEvent1->getSummary());
		
		$proposedChanges2->setSummaryChangeSelected(false);
		$this->assertEquals(false, $proposedChanges2->applyToEvent($futureEvent2, $initialEventEdited));
		$this->assertEquals("Party in ".$marMonthName, $futureEvent2->getSummary());
		
		$proposedChanges3->setSummaryChangeSelected(false);
		$this->assertEquals(false, $proposedChanges3->applyToEvent($futureEvent3, $initialEventEdited));
		$this->assertEquals("Party in ".$aprMonthName, $futureEvent3->getSummary());
		
		
		## Now Set to do update and run and test
		$proposedChanges1->setSummaryChangeSelected(true);
		$this->assertEquals(true, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals("Party like an iguana in ".$febMonthName, $futureEvent1->getSummary());
		
		$proposedChanges2->setSummaryChangeSelected(true);
		$this->assertEquals(true, $proposedChanges2->applyToEvent($futureEvent2, $initialEventEdited));
		$this->assertEquals("Party like an iguana in ".$marMonthName, $futureEvent2->getSummary());
		
		$proposedChanges3->setSummaryChangeSelected(true);
		$this->assertEquals(true, $proposedChanges3->applyToEvent($futureEvent3, $initialEventEdited));
		$this->assertEquals("Party like an iguana in ".$aprMonthName, $futureEvent3->getSummary());
		
		
	}
	
	
	
	function testChangeStartEnd1() {
		
		TimeSource::mock(2014,1,1,9,0,0);
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		
		$initialEvent = new EventModel();
		$initialEvent->setSlug(1);
		$initialEvent->setSummary("Party");
		$initialEvent->setStartAt(getUTCDateTime(2014,1,7,19,0,0));
		$initialEvent->setEndAt(getUTCDateTime(2014,1,7,21,0,0));		
		$eventSet->setInitialEventJustBeforeLastChange($initialEvent);
		
		$initialEventEdited = clone $initialEvent;
		$initialEventEdited->setStartAt(getUTCDateTime(2014,1,7,20,0,0));
		$initialEventEdited->setEndAt(getUTCDateTime(2014,1,7,23,0,0));		
		$eventSet->setInitalEvent($initialEventEdited);
		
		$futureEvent1 = new EventModel();
		$futureEvent1->setSlug(2);
		$futureEvent1->setSummary("Party"); 
		$futureEvent1->setStartAt(getUTCDateTime(2014,1,14,19,0,0));
		$futureEvent1->setEndAt(getUTCDateTime(2014,1,14,21,0,0));		
		
		$eventSet->setFutureEvents( array( $futureEvent1,  ));
		
		$eventHistory = new \models\EventHistoryModel();
		$eventHistory->setFromDataBaseRow(array(
			'event_id'=>null,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>null,
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'-1',
			'description_changed'=>'-1',
			'start_at_changed'=>'1',
			'end_at_changed'=>'1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
		));
		$eventSet->setInitalEventLastChange($eventHistory);
		
		## Detect !!!
		$eventSet->applyChangeToFutureEvents();
		
		## Test Changes Picked Up
		$proposedChanges1 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent1->getSlug());
		$this->assertEquals(false, $proposedChanges1->getSummaryChangePossible());
		$this->assertEquals(false, $proposedChanges1->getSummaryChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getDescriptionChangePossible());
		$this->assertEquals(false, $proposedChanges1->getDescriptionChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getTimezoneChangePossible());
		$this->assertEquals(false, $proposedChanges1->getTimezoneChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getCountryAreaVenueIdChangePossible());
		$this->assertEquals(false, $proposedChanges1->getCountryAreaVenueIdChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getUrlChangePossible());
		$this->assertEquals(false, $proposedChanges1->getUrlChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getIsVirtualChangePossible());
		$this->assertEquals(false, $proposedChanges1->getIsVirtualChangeSelected());
		$this->assertEquals(false, $proposedChanges1->getIsPhysicalChangePossible());
		$this->assertEquals(false, $proposedChanges1->getIsPhysicalChangeSelected());
		$this->assertEquals(true, $proposedChanges1->getStartEndAtChangePossible());
		$this->assertEquals(true, $proposedChanges1->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges1->isAnyChangesPossible());
		
		## Now Set to don't update and run and test
		$proposedChanges1->setStartEndAtChangeSelected(false);
		$this->assertEquals(false, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals(getUTCDateTime(2014,1,14,19,0,0)->getTimestamp(), $futureEvent1->getStartAtInUTC()->getTimestamp());
		$this->assertEquals(getUTCDateTime(2014,1,14,21,0,0)->getTimestamp(), $futureEvent1->getEndAtInUTC()->getTimestamp());
		
		
		## Now Set to do update and run and test
		$proposedChanges1->setStartEndAtChangeSelected(true);
		$this->assertEquals(true, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals(getUTCDateTime(2014,1,14,20,0,0)->getTimestamp(), $futureEvent1->getStartAtInUTC()->getTimestamp());
		$this->assertEquals(getUTCDateTime(2014,1,14,23,0,0)->getTimestamp(), $futureEvent1->getEndAtInUTC()->getTimestamp());
		
	}
	
}

