<?php

namespace repositories;

use models\ImportedEventModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportedEventRepository {

	
	public function loadByEvent(EventModel $event) {
		global $DB;
		$stat = $DB->prepare("SELECT imported_event.* FROM imported_event ".
				" LEFT JOIN imported_event_is_event ON imported_event_is_event.imported_event_id = imported_event.id ".
				"WHERE imported_event_is_event.event_id = :id");
		$stat->execute(array( 'id'=>$event->getId() ));
		if ($stat->rowCount() > 0) {
			$event = new ImportedEventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	public function loadByImportURLIDAndImportId($import_url_id, $import_id) {
		global $DB;
		$stat = $DB->prepare("SELECT imported_event.* FROM imported_event ".
				"WHERE imported_event.import_url_id =:import_url_id AND imported_event.import_id =:import_id");
		$stat->execute(array( 'import_id'=>$import_id, 'import_url_id'=>$import_url_id ));
		if ($stat->rowCount() > 0) {
			$event = new ImportedEventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}

	public function loadByImportURLIDAndId($import_url_id, $id) {
		global $DB;
		$stat = $DB->prepare("SELECT imported_event.* FROM imported_event ".
				"WHERE imported_event.import_url_id =:import_url_id AND imported_event.id =:id");
		$stat->execute(array( 'id'=>$id, 'import_url_id'=>$import_url_id ));
		if ($stat->rowCount() > 0) {
			$event = new ImportedEventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}

	public function create(ImportedEventModel $importedEvent) {
		global $DB;
		$stat = $DB->prepare("INSERT INTO imported_event ( import_url_id, import_id, title, ".
				"description, start_at, end_at, timezone, is_deleted, url, ticket_url, created_at, reoccur ) ".
				" VALUES (  :import_url_id, :import_id, :title, ".
				":description, :start_at, :end_at, :timezone,  '0', :url, :ticket_url, :created_at, :reoccur ) RETURNING id");
		$stat->execute(array(
				'import_url_id'=>$importedEvent->getImportUrlId(), 
				'import_id'=>$importedEvent->getImportId(),
				'title'=>substr($importedEvent->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$importedEvent->getDescription(),
				'start_at'=>$importedEvent->getStartAtInUTC()->format("Y-m-d H:i:s"),
				'end_at'=>$importedEvent->getEndAtInUTC()->format("Y-m-d H:i:s"),
				'timezone'=>$importedEvent->getTimezone(),				
				'url'=>$importedEvent->getUrl(),				
				'ticket_url'=>$importedEvent->getTicketUrl(),
				'reoccur' => $importedEvent->getReoccur() ? json_encode($importedEvent->getReoccur()) : null,
				'created_at'=>\TimeSource::getFormattedForDataBase(),
			));
		$data = $stat->fetch();
		$importedEvent->setId($data['id']);
	}
	
	public function edit(ImportedEventModel $importedEvent) {
		global $DB;
		$stat = $DB->prepare("UPDATE imported_event SET title=:title, description=:description, ".
				"start_at=:start_at, end_at=:end_at, timezone=:timezone,  is_deleted='1', url = :url, ".
				"ticket_url = :ticket_url, reoccur=:reoccur WHERE id=:id");
		$stat->execute(array(
			'id'=>$importedEvent->getId(),
			'title'=>substr($importedEvent->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
			'description'=>$importedEvent->getDescription(),
			'start_at'=>$importedEvent->getStartAtInUTC()->format("Y-m-d H:i:s"),
			'end_at'=>$importedEvent->getEndAtInUTC()->format("Y-m-d H:i:s"),
			'timezone'=>$importedEvent->getTimezone(),				
			'url'=>$importedEvent->getUrl(),				
			'ticket_url'=>$importedEvent->getTicketUrl(),
			'reoccur' => $importedEvent->getReoccur() ? json_encode($importedEvent->getReoccur()) : null,
		));
	}
	
	public function delete(ImportedEventModel $importedEvent) {
		global $DB;
		$stat = $DB->prepare("UPDATE imported_event SET is_deleted='1' WHERE id=:id");
		$stat->execute(array(
			'id'=>$importedEvent->getId(),
		));
	}
	
}

