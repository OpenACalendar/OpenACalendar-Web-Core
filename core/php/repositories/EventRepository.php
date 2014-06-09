<?php


namespace repositories;

use models\EventModel;
use models\EventHistoryModel;
use models\SiteModel;
use models\GroupModel;
use models\VenueModel;
use models\UserAccountModel;
use \models\ImportedEventModel;
use repositories\UserWatchesGroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class EventRepository {
	
	
	public function create(EventModel $event, SiteModel $site, UserAccountModel $creator = null, 
			GroupModel $group = null, $additionalGroups = null, ImportedEventModel $importedEvent = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM event_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$event->setSlug($data['c'] + 1);

			$stat = $DB->prepare("INSERT INTO event_information (site_id, slug, summary,description,start_at,end_at,".
				" created_at, event_recur_set_id,venue_id,country_id,timezone, ".
				" url, is_physical, is_virtual, area_id) ".
					" VALUES (:site_id, :slug, :summary, :description, :start_at, :end_at, ".
						" :created_at, :event_recur_set_id,:venue_id,:country_id,:timezone, ".
						" :url, :is_physical, :is_virtual, :area_id) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$event->getSlug(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'event_recur_set_id'=>$event->getEventRecurSetId(),
					'country_id'=>$event->getCountryId(),
					'venue_id'=>$event->getVenueId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'timezone'=>$event->getTimezone(),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
				));
			$data = $stat->fetch();
			$event->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, ".
				" user_account_id  , created_at,venue_id,country_id,timezone,".
				" url, is_physical, is_virtual, area_id, is_new) VALUES ".
					" (:event_id, :summary, :description, :start_at, :end_at, ".
						" :user_account_id  , :created_at,:venue_id,:country_id,:timezone,".
						" :url, :is_physical, :is_virtual, :area_id, '1')");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'user_account_id'=>($creator ? $creator->getId(): null),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'country_id'=>$event->getCountryId(),
					'venue_id'=>$event->getVenueId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'timezone'=>$event->getTimezone(),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
				));
			
			if ($group || $additionalGroups) {
				$stat = $DB->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,is_main_group) ".
						"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:is_main_group)");
				if ($group) {
					$stat->execute(array(
							'group_id'=>$group->getId(),
							'event_id'=>$event->getId(),
							'added_by_user_account_id'=>($creator ? $creator->getId(): null),
							'added_at'=>\TimeSource::getFormattedForDataBase(),
							'is_main_group'=>1,
						));
				}
				if ($additionalGroups && is_array($additionalGroups)) {
					foreach ($additionalGroups as $additionalGroup) {
						if ($additionalGroup->getId() != $group->getId()) {
							$stat->execute(array(
									'group_id'=>$additionalGroup->getId(),
									'event_id'=>$event->getId(),
									'added_by_user_account_id'=>($creator ? $creator->getId(): null),
									'added_at'=>\TimeSource::getFormattedForDataBase(),
									'is_main_group'=>0,
								));
						}
					}
				}
			}
			
			
			if ($creator) {
				if ($event->getGroupId()) {
					$ufgr = new UserWatchesGroupRepository();
					$ufgr->startUserWatchingGroupIdIfNotWatchedBefore($creator, $event->getGroupId());
				} else {
					// TODO watch site?
				}
			}
			
			if ($importedEvent) {
				$stat = $DB->prepare("INSERT INTO imported_event_is_event (imported_event_id, event_id, created_at) ".
						"VALUES (:imported_event_id, :event_id, :created_at)");
				$stat->execute(array(
					'imported_event_id'=>$importedEvent->getId(),
					'event_id'=>$event->getId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			}
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				" WHERE event_information.slug =:slug AND event_information.site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			// Now, we currently have 2 ways of linking imported events to events
			// old way - flags on event
			// new way - seperate models
			// The below code checks the new way of linking and adds it if it finds anything
			$repo = new \repositories\ImportedEventRepository();
			$importedEvent = $repo->loadByEvent($event);
			if ($importedEvent) {
				$event->setImportId($importedEvent->getImportId());
				$event->setImportUrlId($importedEvent->getImportUrlId());
			}
			// ... and return
			return $event;
		}
	}
	
	
	/**
	 * Note you can only edit undeleted events.
	 * @global type $DB
	 * @param EventModel $event
	 * @param UserAccountModel $creator
	 * @param EventHistoryModel $fromHistory 
	 */
	public function edit(EventModel $event,  UserAccountModel $creator = null, EventHistoryModel $fromHistory = null ) {
		if ($event->getIsDeleted()) {
			throw new \Exception("Can't edit deleted events!");
		}
		
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE event_information  SET summary=:summary, description=:description, ".
					"start_at=:start_at, end_at=:end_at, is_deleted='0', area_id=:area_id, ".
					" venue_id=:venue_id, country_id=:country_id, timezone=:timezone, ".
					"url=:url, is_physical=:is_physical, is_virtual=:is_virtual ".
					"WHERE id=:id");
			$stat->execute(array(
					'id'=>$event->getId(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'venue_id'=>$event->getVenueId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'country_id'=>$event->getCountryId(),
					'timezone'=>$event->getTimezone(),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
				));
			
			$stat = $DB->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, user_account_id  , ".
					"created_at, reverted_from_created_at,venue_id,country_id,timezone,".
					"url, is_physical, is_virtual, area_id) VALUES ".
					"(:event_id, :summary, :description, :start_at, :end_at, :user_account_id  , ".
					":created_at, :reverted_from_created_at,:venue_id,:country_id,:timezone,"."
						:url, :is_physical, :is_virtual, :area_id)");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'venue_id'=>$event->getVenueId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'country_id'=>$event->getCountryId(),
					'timezone'=>$event->getTimezone(),
					'user_account_id'=>($creator ? $creator->getId(): null),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'reverted_from_created_at'=> ($fromHistory ? date("Y-m-d H:i:s",$fromHistory->getCreatedAtTimeStamp()):null),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
				));
			
			
			if ($creator) {
				if ($event->getGroupId()) {
					$ufgr = new UserWatchesGroupRepository();
					$ufgr->startUserWatchingGroupIdIfNotWatchedBefore($creator, $event->getGroupId());
				} else {
					// TODO watch site?
				}
			}
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function delete(EventModel $event,  UserAccountModel $creator=null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE event_information  SET is_deleted='1' WHERE id=:id");
			$stat->execute(array(
					'id'=>$event->getId(),
				));
			
			$stat = $DB->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, user_account_id  , ".
					"created_at,venue_id,country_id,timezone,".
					"url, is_physical, is_virtual, is_deleted) VALUES ".
					"(:event_id, :summary, :description, :start_at, :end_at, :user_account_id  , ".
					":created_at,:venue_id,:country_id,:timezone,"."
						:url, :is_physical, :is_virtual, '1')");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAt()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAt()->format("Y-m-d H:i:s"),
					'venue_id'=>$event->getVenueId(),
					'country_id'=>$event->getCountryId(),
					'timezone'=>$event->getTimezone(),
					'user_account_id'=>($creator ? $creator->getId(): null),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
				));
			
			
			// TODO if in group, watch

			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
		
		$event->setIsDeleted(true);
	}
	
	public function loadLastNonDeletedNonImportedByStartTimeInSiteId($siteID) {
		global $DB;
		$stat = $DB->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				"WHERE event_information.site_id =:sid AND event_information.import_url_id is null AND event_information.is_deleted = '0' ORDER BY event_information.start_at DESC LIMIT 1");
		$stat->execute(array( 'sid'=>$siteID ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	public function loadLastNonDeletedNonImportedByStartTimeInGroupId($groupID) {
		global $DB;
		// We haven't got a " AND event_in_group.is_main_group = '1' " search term so the group_title & group_id returned may not be from the main group
		// but given where this is used, that's ok for now.
		// We need to make sure the search by group clause works.
		$stat = $DB->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				"WHERE group_information.id =:gid AND event_information.import_url_id is null AND event_information.is_deleted = '0' ORDER BY event_information.start_at DESC LIMIT 1");
		$stat->execute(array( 'gid'=>$groupID ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	public function loadByImportURLIDAndImportId($importURLID, $importID) {
			global $DB;
		$stat = $DB->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				"WHERE event_information.import_url_id =:import_url_id AND event_information.import_id =:import_id");
		$stat->execute(array( 'import_id'=>$importID, 'import_url_id'=>$importURLID ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	
	/**
	 * 
	 * This is a bit broken - in theory this could return multiple events (in the case of a imported event with recurrence) 
	 * But for now that can't happen so just return one event.
	 * 
	 * @global \repositories\type $DB
	 * @param \models\ImportedEventModel $importedEvent
	 * @return \models\EventModel
	 */
	public function loadByImportedEvent(\models\ImportedEventModel $importedEvent) {
			global $DB;
		$stat = $DB->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
				" JOIN imported_event_is_event ON imported_event_is_event.event_id = event_information.id ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				" WHERE imported_event_is_event.imported_event_id = :id");
		$stat->execute(array( 'id'=>$importedEvent->getId() ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
	
	
	public function moveAllFutureEventsAtVenueToNoSetVenue(VenueModel $venue, UserAccountModel $user) {
			global $DB;
			
			$statFetch = $DB->prepare("SELECT event_information.* FROM event_information WHERE venue_id = :venue_id AND start_at > :start_at AND is_deleted='0'");
			$statUpdateEvent = $DB->prepare("UPDATE event_information  SET venue_id=null, area_id=:area_id WHERE id=:id");	
			$statAddHistory = $DB->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, user_account_id  , ".
					"created_at,venue_id,country_id,timezone,".
					"url, is_physical, is_virtual, is_deleted) VALUES ".
					"(:event_id, :summary, :description, :start_at, :end_at, :user_account_id  , ".
					":created_at,:venue_id,:country_id,:timezone,"."
						:url, :is_physical, :is_virtual, '0')");
			$statFetch->execute(array('venue_id'=>$venue->getId(), 'start_at'=>\TimeSource::getFormattedForDataBase()));
			while($data = $statFetch->fetch()) {
				$event = new EventModel();
				$event->setFromDataBaseRow($data);
				$event->setVenueId(null);
				$event->setAreaId($venue->getAreaId());
				
				try {
					$DB->beginTransaction();

					$statUpdateEvent->execute(array(
											'id'=>$event->getId(),
											'area_id'=>$event->getAreaId(),
										));
										
					$statAddHistory->execute(array(
											'event_id'=>$event->getId(),
											'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
											'description'=>$event->getDescription(),
											'start_at'=>$event->getStartAt()->format("Y-m-d H:i:s"),
											'end_at'=>$event->getEndAt()->format("Y-m-d H:i:s"),
											'venue_id'=>$event->getVenueId(),
											'country_id'=>$event->getCountryId(),
											'timezone'=>$event->getTimezone(),
											'user_account_id'=>($user ? $user->getId(): null),				
											'created_at'=>\TimeSource::getFormattedForDataBase(),
											'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
											'is_physical'=>$event->getIsPhysical()?1:0,
											'is_virtual'=>$event->getIsVirtual()?1:0,
										));
			
					$DB->commit();
				} catch (Exception $e) {
					$DB->rollBack();
				}
					
			}
		
	}
	
	public function loadEventJustBeforeEdit(EventModel $event, EventHistoryModel $eventHistory) {
		global $DB;
		$eventOut = clone $event;
		
		/**
		 * At moment the last edit has all state, changed or not.
		 * 
		 * In future we may only store state in event_history for changed fields to save space.
		 * 
		 * When we do that, we'll have to iterate back over multiple event_histories until we have all fields.
		 */
		$stat = $DB->prepare("SELECT event_history.* FROM event_history ".
				"WHERE event_history.event_id = :id AND event_history.created_at < :cat ".
				"ORDER BY event_history.created_at DESC LIMIT 1");
		$stat->execute(array( 
				'id'=>$event->getId(), 
				'cat'=>date("Y-m-d H:i:s",$eventHistory->getCreatedAtTimeStamp()) 
			));
		if ($stat->rowCount() > 0) {
			$eventHistoryToApply = new EventHistoryModel();
			$eventHistoryToApply->setFromDataBaseRow($stat->fetch());
			$eventOut->setFromHistory($eventHistoryToApply);
		}
		
		return $eventOut;
	}
}

