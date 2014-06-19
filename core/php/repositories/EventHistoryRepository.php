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
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$eventhistory->getId(),'at'=>$eventhistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$eventhistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new eventHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$eventhistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE event_history SET ".
				" is_new = :is_new, ".
				" summary_changed = :summary_changed   , ".
				" description_changed = :description_changed   , ".
				" start_at_changed = :start_at_changed   , ".
				" end_at_changed = :end_at_changed   , ".
				" is_deleted_changed = :is_deleted_changed   , ".
				" country_id_changed = :country_id_changed   , ".
				" timezone_changed = :timezone_changed   , ".
				" venue_id_changed = :venue_id_changed   , ".
				" url_changed = :url_changed  , ".
				" ticket_url_changed = :ticket_url_changed  , ".
				" is_virtual_changed = :is_virtual_changed   , ".
				" is_physical_changed = :is_physical_changed   , ".
				" area_id_changed = :area_id_changed    ".
				"WHERE event_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$eventhistory->getId(),
				'created_at'=>$eventhistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'is_new'=>$eventhistory->getIsNew()?1:0,
				'summary_changed'=> $eventhistory->getSummaryChanged() ? 1 : -1,
				'description_changed'=> $eventhistory->getDescriptionChanged() ? 1 : -1,
				'start_at_changed'=> $eventhistory->getStartAtChanged() ? 1 : -1,
				'end_at_changed'=> $eventhistory->getEndAtChanged() ? 1 : -1,
				'is_deleted_changed'=> $eventhistory->getIsDeletedChanged() ? 1 : -1,
				'country_id_changed'=> $eventhistory->getCountryIdChanged() ? 1 : -1,
				'timezone_changed'=> $eventhistory->getTimezoneChanged() ? 1 : -1,
				'venue_id_changed'=> $eventhistory->getVenueIdChanged() ? 1 : -1,
				'url_changed'=> $eventhistory->getUrlChanged() ? 1 : -1,
				'ticket_url_changed'=> $eventhistory->getTicketUrlChanged() ? 1 : -1,
				'is_virtual_changed'=> $eventhistory->getIsVirtualChanged() ? 1 : -1,
				'is_physical_changed'=> $eventhistory->getIsPhysicalChanged() ? 1 : -1,
				'area_id_changed'=> $eventhistory->getAreaIdChanged() ? 1 : -1,
			));
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


