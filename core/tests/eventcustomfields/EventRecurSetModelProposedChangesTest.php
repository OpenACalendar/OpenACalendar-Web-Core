<?php

namespace tests\eventcustomfields;

use models\EventCustomFieldDefinitionModel;
use models\EventModel;
use models\EventRecurSetModel;
use models\EventHistoryModel;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelProposedChangesTest extends \BaseAppTest {
	
	/*
	 */
	function testChangCustomField1() {
		
		TimeSource::mock(2014,1,1,9,0,0);

		$customFieldDefinition1 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition1->setId(1);
		$customFieldDefinition1->setExtensionId('org.openacalendar');
		$customFieldDefinition1->setType('TextSingleLine');
		$customFieldDefinition1->setKey('cats');
		$customFieldDefinition1->setLabel('cats');

		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		$eventSet->setCustomFields(array($customFieldDefinition1));


		$initialEvent = new EventModel();
		$initialEvent->setSlug(1);
		$initialEvent->setCustomField($customFieldDefinition1, "Dogs go miaow"); // this is the inital wrong value
		$initialEvent->setStartAt(getUTCDateTime(2014,1,7,19,0,0));
		$initialEvent->setEndAt(getUTCDateTime(2014,1,7,21,0,0));		
		$eventSet->setInitialEventJustBeforeLastChange($initialEvent);
		
		$initialEventEdited = clone $initialEvent;
		$initialEventEdited->setCustomField($customFieldDefinition1, "Dogs go woof"); // this is the later corrected value
		$eventSet->setInitalEvent($initialEventEdited);
		
		$futureEvent1 = new EventModel();
		$futureEvent1->setSlug(2);
		$futureEvent1->setCustomField($customFieldDefinition1, "Dogs go miaow"); // this is the earlier wrong value that we will want to overwrite
		$futureEvent1->setStartAt(getUTCDateTime(2014,1,14,19,0,0));
		$futureEvent1->setEndAt(getUTCDateTime(2014,1,14,21,0,0));		
		
		$futureEvent2 = new EventModel();
		$futureEvent2->setSlug(3);
		$futureEvent2->setCustomField($customFieldDefinition1, "Dogs go woof"); // this value has already been corrected
		$futureEvent2->setStartAt(getUTCDateTime(2014,1,21,19,0,0));
		$futureEvent2->setEndAt(getUTCDateTime(2014,1,21,21,0,0));		
		
		$futureEvent3 = new EventModel();
		$futureEvent3->setSlug(4);
		$futureEvent3->setCustomField($customFieldDefinition1, "Dogs go miaowwwwwwww"); // this value has already been edited. Possible to overwrite but not recommended
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
			'custom_fields'=>json_encode(array('1'=>'Dogs go woof')),
			'custom_fields_changed'=>json_encode(array('1'=>1)),
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
		$this->assertEquals(false, $proposedChanges1->getStartEndAtChangePossible());
		$this->assertEquals(false, $proposedChanges1->getStartEndAtChangeSelected());
		$this->assertEquals(true, $proposedChanges1->getCustomFieldChangePossible($customFieldDefinition1));
		$this->assertEquals(true, $proposedChanges1->getCustomFieldChangeSelected($customFieldDefinition1));
		
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
		$this->assertEquals(false, $proposedChanges2->getCustomFieldChangePossible($customFieldDefinition1));
		$this->assertEquals(false, $proposedChanges2->getCustomFieldChangeSelected($customFieldDefinition1));
		
		$proposedChanges3 = $eventSet->getFutureEventsProposedChangesForEventSlug($futureEvent3->getSlug());
		$this->assertEquals(false, $proposedChanges3->getSummaryChangePossible());
		$this->assertEquals(false, $proposedChanges3->getSummaryChangeSelected());
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
		$this->assertEquals(true, $proposedChanges3->getCustomFieldChangePossible($customFieldDefinition1));
		$this->assertEquals(false, $proposedChanges3->getCustomFieldChangeSelected($customFieldDefinition1));
		
		
		## Now Set to don't update and run and test
		$proposedChanges1->setCustomFieldChangeSelected($customFieldDefinition1, false);
		$this->assertEquals(false, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals("Dogs go miaow", $futureEvent1->getCustomField($customFieldDefinition1));
		
		
		## Now Set to do update and run and test
		$proposedChanges1->setCustomFieldChangeSelected($customFieldDefinition1, true);
		$this->assertEquals(true, $proposedChanges1->applyToEvent($futureEvent1, $initialEventEdited));
		$this->assertEquals("Dogs go woof", $futureEvent1->getCustomField($customFieldDefinition1));
		
	}
	
	
	


	
}

