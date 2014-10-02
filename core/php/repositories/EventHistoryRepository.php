<?php

namespace repositories;

use models\EventModel;
use models\EventHistoryModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventHistoryRepository {

	
	public function loadByEventAndtimeStamp(EventModel $event, $timestamp) {
		global $DB;
		$stat = $DB->prepare("SELECT event_history.* FROM event_history ".
				"WHERE event_history.event_id =:id AND event_history.created_at =:cat");
		$stat->execute(array( 'id'=>$event->getId(), 'cat'=>date("Y-m-d H:i:s",$timestamp) ));
		if ($stat->rowCount() > 0) {
			$event = new EventHistoryModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	public function ensureChangedFlagsAreSet(EventHistoryModel $eventhistory) {
		global $DB;
		
		// do we already have them?
		if (!$eventhistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM event_history WHERE event_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC");
		$stat->execute(array('id'=>$eventhistory->getId(),'at'=>$eventhistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$eventhistory->setChangedFlagsFromNothing();
		} else {
			while($eventhistory->isAnyChangeFlagsUnknown() && $lastHistoryData = $stat->fetch()) {
				$lastHistory = new GroupHistoryModel();
				$lastHistory->setFromDataBaseRow($lastHistoryData);
				$eventhistory->setChangedFlagsFromLast($lastHistory);
			}
		}



		// Save back to DB
		$sqlFields = array();
		$sqlParams = array(
			'id'=>$eventhistory->getId(),
			'created_at'=>$eventhistory->getCreatedAt()->format("Y-m-d H:i:s"),
			'is_new'=>$eventhistory->getIsNew()?1:0,
		);


		if ($eventhistory->getSummaryChangedKnown()) {
			$sqlFields[] = " summary_changed = :summary_changed ";
			$sqlParams['summary_changed'] = $eventhistory->getSummaryChanged() ? 1 : -1;
		}
		if ($eventhistory->getDescriptionChangedKnown()) {
			$sqlFields[] = " description_changed = :description_changed ";
			$sqlParams['description_changed'] = $eventhistory->getDescriptionChanged() ? 1 : -1;
		}
		if ($eventhistory->getStartAtChangedKnown()) {
			$sqlFields[] = " start_at_changed = :start_at_changed ";
			$sqlParams['start_at_changed'] = $eventhistory->getStartAtChanged() ? 1 : -1;
		}
		if ($eventhistory->getEndAtChangedKnown()) {
			$sqlFields[] = " end_at_changed = :end_at_changed ";
			$sqlParams['end_at_changed'] = $eventhistory->getEndAtChanged() ? 1 : -1;
		}
		if ($eventhistory->getVenueIdChangedKnown()) {
			$sqlFields[] = " venue_id_changed = :venue_id_changed ";
			$sqlParams['venue_id_changed'] = $eventhistory->getVenueIdChanged() ? 1 : -1;
		}
		if ($eventhistory->getAreaIdChangedKnown()) {
			$sqlFields[] = " area_id_changed = :area_id_changed ";
			$sqlParams['area_id_changed'] = $eventhistory->getAreaIdChanged() ? 1 : -1;
		}
		if ($eventhistory->getCountryIdChangedKnown()) {
			$sqlFields[] = " country_id_changed = :country_id_changed ";
			$sqlParams['country_id_changed'] = $eventhistory->getCountryIdChanged() ? 1 : -1;
		}
		if ($eventhistory->getTimezoneChangedKnown()) {
			$sqlFields[] = " timezone_changed = :timezone_changed ";
			$sqlParams['timezone_changed'] = $eventhistory->getTimezoneChanged() ? 1 : -1;
		}
		if ($eventhistory->getUrlChangedKnown()) {
			$sqlFields[] = " url_changed = :url_changed ";
			$sqlParams['url_changed'] = $eventhistory->getUrlChanged() ? 1 : -1;
		}
		if ($eventhistory->getTicketUrlChangedKnown()) {
			$sqlFields[] = " ticket_url_changed = :ticket_url_changed ";
			$sqlParams['ticket_url_changed'] = $eventhistory->getTicketUrlChanged() ? 1 : -1;
		}
		if ($eventhistory->getIsPhysicalChangedKnown()) {
			$sqlFields[] = " is_physical_changed = :is_physical_changed ";
			$sqlParams['is_physical_changed'] = $eventhistory->getIsPhysicalChanged() ? 1 : -1;
		}
		if ($eventhistory->getIsVirtualChangedKnown()) {
			$sqlFields[] = " is_virtual_changed = :is_virtual_changed ";
			$sqlParams['is_virtual_changed'] = $eventhistory->getIsVirtualChanged() ? 1 : -1;
		}
		if ($eventhistory->getIsCancelledChangedKnown()) {
			$sqlFields[] = " is_cancelled_changed = :is_cancelled_changed ";
			$sqlParams['is_cancelled_changed'] = $eventhistory->getIsCancelledChanged() ? 1 : -1;
		}
		if ($eventhistory->getIsDeletedChangedKnown()) {
			$sqlFields[] = " is_deleted_changed = :is_deleted_changed ";
			$sqlParams['is_deleted_changed'] = $eventhistory->getIsDeletedChanged() ? 1 : -1;
		}
		if ($eventhistory->getIsDuplicateOfIdChangedKnown()) {
			$sqlFields[] = " is_duplicate_of_id_changed = :is_duplicate_of_id_changed ";
			$sqlParams['is_duplicate_of_id_changed'] = $eventhistory->getIsDuplicateOfIdChanged() ? 1 : -1;
		}


		$statUpdate = $DB->prepare("UPDATE event_history SET ".
			" is_new = :is_new, ".
			implode(" , ",$sqlFields).
			" WHERE event_id = :id AND created_at = :created_at");
		$statUpdate->execute($sqlParams);

	}
	
	
	
	
	public function loadByEventAndlastEditByUser(EventModel $event, UserAccountModel $user) {
		global $DB;
		$stat = $DB->prepare("SELECT event_history.* FROM event_history ".
				" WHERE event_history.event_id = :id AND event_history.user_account_id = :user ".
				" ORDER BY event_history.created_at DESc");
		$stat->execute(array( 
				'id'=>$event->getId(), 
				'user'=>$user->getId() 
			));
		if ($stat->rowCount() > 0) {
			$event = new EventHistoryModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	
}


