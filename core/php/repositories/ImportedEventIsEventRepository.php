<?php


namespace repositories;

use models\ImportedEventModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportedEventIsEventRepository {
	
	public function createLink(ImportedEventModel $importedEvent, EventModel $event) {
		global $DB;
		$stat = $DB->prepare("INSERT INTO imported_event_is_event (imported_event_id, event_id, created_at) ".
				"VALUES (:imported_event_id, :event_id, :created_at)");
		$stat->execute(array(
			'imported_event_id'=>$importedEvent->getId(),
			'event_id'=>$event->getId(),
			'created_at'=>\TimeSource::getFormattedForDataBase(),
		));
	}
	
	
	
}
