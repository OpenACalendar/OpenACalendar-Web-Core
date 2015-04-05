<?php

namespace tests\eventcustomfields;

use models\EventCustomFieldDefinitionModel;
use models\EventModel;
use models\EventRecurSetModel;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelGetNewWeeklyEventsTest extends \BaseAppTest {

	
	/*
	 * Basic test. Across DST change.
	 * 
	 */
	function testCustomFieldChange1() {
		
		TimeSource::mock(2013,3,1,9,0,0);

		$customFieldDefinition1 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition1->setId(1);
		$customFieldDefinition1->setExtensionId('org.openacalendar');
		$customFieldDefinition1->setType('TextSingleLine');
		$customFieldDefinition1->setKey('cats');
		$customFieldDefinition1->setLabel('cats');

		$event = new EventModel();
		$event->setStartAt(getUTCDateTime(2013,3,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,3,1,21,0,0));
		$event->setCustomField($customFieldDefinition1, "MANY");
		
		$eventSet = new EventRecurSetModel();
		$eventSet->setTimeZoneName('Europe/London');
		$eventSet->setCustomFields( array ($customFieldDefinition1));
		
		$newEvents = $eventSet->getNewWeeklyEvents($event, 60);
		
		$this->assertTrue(count($newEvents) >= 8);

		$this->assertTrue($newEvents[0]->hasCustomField($customFieldDefinition1));
		$this->assertEquals("MANY", $newEvents[0]->getCustomField($customFieldDefinition1));

		$this->assertTrue($newEvents[1]->hasCustomField($customFieldDefinition1));
		$this->assertEquals("MANY", $newEvents[1]->getCustomField($customFieldDefinition1));

		$this->assertTrue($newEvents[2]->hasCustomField($customFieldDefinition1));
		$this->assertEquals("MANY", $newEvents[2]->getCustomField($customFieldDefinition1));

		$this->assertTrue($newEvents[3]->hasCustomField($customFieldDefinition1));
		$this->assertEquals("MANY", $newEvents[3]->getCustomField($customFieldDefinition1));

		// DST shift happens here! The cats do not care.
		$this->assertTrue($newEvents[4]->hasCustomField($customFieldDefinition1));
		$this->assertEquals("MANY", $newEvents[4]->getCustomField($customFieldDefinition1));

		$this->assertTrue($newEvents[5]->hasCustomField($customFieldDefinition1));
		$this->assertEquals("MANY", $newEvents[5]->getCustomField($customFieldDefinition1));

		
		
	}
	
	
	
}


