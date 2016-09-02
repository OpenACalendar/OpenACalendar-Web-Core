<?php


namespace repositories;

use models\EventEditMetaDataModel;
use models\EventModel;
use models\EventHistoryModel;
use models\SiteModel;
use models\GroupModel;
use models\VenueModel;
use models\UserAccountModel;
use \models\ImportedEventModel;
use repositories\builders\UserAtEventRepositoryBuilder;
use repositories\UserWatchesGroupRepository;
use dbaccess\EventDBAccess;
use Silex\Application;
use Slugify;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class EventRepository {

    /** @var Application */
    private  $app;


	/** @var  \dbaccess\EventDBAccess */
	protected $eventDBAccess;


	function __construct(Application $app)
	{
        $this->app = $app;
		$this->eventDBAccess = new EventDBAccess($app);
	}


	/**
	 * @deprecated
	 */
	public function create(EventModel $event, SiteModel $site, UserAccountModel $creator = null, 
			GroupModel $group = null, $additionalGroups = null, ImportedEventModel $importedEvent = null, $tags=array(), $medias=array())
	{
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($creator);
		$this->createWithMetaData($event, $site, $eventEditMetaDataModel, $group, $additionalGroups, $importedEvent, $tags, $medias);
	}


	public function createWithMetaData(EventModel $event, SiteModel $site, EventEditMetaDataModel $eventEditMetaDataModel,
			GroupModel $group = null, $additionalGroups = null, ImportedEventModel $importedEvent = null, $tags=array(), $medias=array()) {
        $slugify = new Slugify($this->app);
		try {
            $this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM event_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$event->setSlug($data['c'] + 1);

			$stat = $this->app['db']->prepare("INSERT INTO event_information (site_id, slug, slug_human, summary,description,start_at,end_at,".
				" created_at, event_recur_set_id,venue_id,country_id,timezone, ".
				" url, ticket_url, is_physical, is_virtual, area_id, approved_at, is_deleted, is_cancelled, custom_fields) ".
					" VALUES (:site_id, :slug, :slug_human, :summary, :description, :start_at, :end_at, ".
						" :created_at, :event_recur_set_id,:venue_id,:country_id,:timezone, ".
						" :url, :ticket_url, :is_physical, :is_virtual, :area_id, :approved_at, '0', '0', :custom_fields) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$event->getSlug(),
                    'slug_human'=>$slugify->process($event->getSummary()),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'event_recur_set_id'=>$event->getEventRecurSetId(),
					'country_id'=>$event->getCountryId(),
					'venue_id'=>$event->getVenueId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'timezone'=>$event->getTimezone(),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'ticket_url'=>substr($event->getTicketUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
					'custom_fields'=>json_encode($event->getCustomFields()),
				));
			$data = $stat->fetch();
			$event->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, ".
				" user_account_id  , created_at,venue_id,country_id,timezone,".
				" url, ticket_url, is_physical, is_virtual, area_id, is_new, approved_at, is_deleted, is_cancelled, edit_comment, custom_fields, from_ip) VALUES ".
					" (:event_id, :summary, :description, :start_at, :end_at, ".
						" :user_account_id  , :created_at,:venue_id,:country_id,:timezone,".
						" :url, :ticket_url, :is_physical, :is_virtual, :area_id, '1', :approved_at, '0', '0', :edit_comment, :custom_fields, :from_ip)");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'user_account_id'=>($eventEditMetaDataModel->getUserAccount() ? $eventEditMetaDataModel->getUserAccount()->getId(): null),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'country_id'=>$event->getCountryId(),
					'venue_id'=>$event->getVenueId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'timezone'=>$event->getTimezone(),
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'ticket_url'=>substr($event->getTicketUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
					'edit_comment'=>$eventEditMetaDataModel->getEditComment(),
					'custom_fields'=>json_encode($event->getCustomFields()),
                    'from_ip' => $eventEditMetaDataModel->getIp(),
				));

			// Group can be passed as model or as field on event!
			if (!$group && $event->getGroupId()) {
				$group = new GroupModel();
				$group->setId($event->getGroupId());
			}

			if ($group || $additionalGroups) {
				$stat = $this->app['db']->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,is_main_group,addition_approved_at) ".
						"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:is_main_group,:addition_approved_at)");
				if ($group) {
					$stat->execute(array(
							'group_id'=>$group->getId(),
							'event_id'=>$event->getId(),
							'added_by_user_account_id'=>($eventEditMetaDataModel->getUserAccount() ? $eventEditMetaDataModel->getUserAccount()->getId(): null),
							'added_at'=>$this->app['timesource']->getFormattedForDataBase(),
							'addition_approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
							'is_main_group'=>1,
						));
				}
				if ($additionalGroups && is_array($additionalGroups)) {
					foreach ($additionalGroups as $additionalGroup) {
						if ($additionalGroup->getId() != $group->getId()) {
							$stat->execute(array(
									'group_id'=>$additionalGroup->getId(),
									'event_id'=>$event->getId(),
									'added_by_user_account_id'=>($eventEditMetaDataModel->getUserAccount() ? $eventEditMetaDataModel->getUserAccount()->getId(): null),
									'added_at'=>$this->app['timesource']->getFormattedForDataBase(),
									'addition_approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
									'is_main_group'=>0,
								));
						}
					}
				}
			}
			
			
			if ($eventEditMetaDataModel->getUserAccount()) {
				if ($event->getGroupId()) {
					$ufgr = new UserWatchesGroupRepository($this->app);
					$ufgr->startUserWatchingGroupIdIfNotWatchedBefore($eventEditMetaDataModel->getUserAccount(), $event->getGroupId());
				} else {
					// TODO watch site?
				}
			}
			
			if ($importedEvent) {
				$stat = $this->app['db']->prepare("INSERT INTO imported_event_is_event (imported_event_id, event_id, created_at) ".
						"VALUES (:imported_event_id, :event_id, :created_at)");
				$stat->execute(array(
					'imported_event_id'=>$importedEvent->getId(),
					'event_id'=>$event->getId(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			}
			
			if ($tags) {	
				$stat = $this->app['db']->prepare("INSERT INTO event_has_tag (tag_id,event_id,added_by_user_account_id,added_at,addition_approved_at) ".
					"VALUES (:tag_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at)");
				foreach($tags as $tag) {
						$stat->execute(array(
							'tag_id'=>$tag->getId(),
							'event_id'=>$event->getId(),
							'added_by_user_account_id'=>($eventEditMetaDataModel->getUserAccount()?$eventEditMetaDataModel->getUserAccount()->getId():null),
							'added_at'=>  $this->app['timesource']->getFormattedForDataBase(),
							'addition_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
						));
				}
			}

			if ($medias) {
				$stat = $this->app['db']->prepare("INSERT INTO media_in_event (event_id,media_id,added_by_user_account_id,added_at,addition_approved_at) ".
					"VALUES (:event_id,:media_id,:added_by_user_account_id,:added_at,:addition_approved_at)");
				foreach($medias as $media) {
					$stat->execute(array(
						'event_id'=>$event->getId(),
						'media_id'=>$media->getId(),
						'added_by_user_account_id'=>($eventEditMetaDataModel->getUserAccount()?$eventEditMetaDataModel->getUserAccount()->getId():null),
						'added_at'=>  $this->app['timesource']->getFormattedForDataBase(),
						'addition_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					));
				}
			}

			if ($eventEditMetaDataModel->getCreatedFromNewEventDraftID()) {
				$stat = $this->app['db']->prepare("UPDATE new_event_draft_information SET event_id=:event_id WHERE id=:id");
				$stat->execute(array(
					'event_id'=>$event->getId(),
					'id'=>$eventEditMetaDataModel->getCreatedFromNewEventDraftID(),
				));

				$stat = $this->app['db']->prepare("INSERT INTO new_event_draft_history (new_event_draft_id, event_id, user_account_id,created_at,details_changed,was_existing_event_id_changed,not_duplicate_events_changed) ".
					"VALUES (:new_event_draft_id, :event_id, :user_account_id,:created_at,-2,-2,-2 )");
				$stat->execute(array(
					'new_event_draft_id'=>$eventEditMetaDataModel->getCreatedFromNewEventDraftID(),
					'event_id'=>$event->getId(),
					'user_account_id'=>($eventEditMetaDataModel->getUserAccount()?$eventEditMetaDataModel->getUserAccount()->getId():null),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}


	public function loadByID($id) {
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id FROM event_information ".
			" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
			" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
			" WHERE event_information.id =:id");
		$stat->execute(array( 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			// Now, we currently have 2 ways of linking imported events to events
			// old way - flags on event
			// new way - seperate models
			// The below code checks the new way of linking and adds it if it finds anything
			$repo = new \repositories\ImportedEventRepository($this->app);
			$importedEvent = $repo->loadByEvent($event);
			if ($importedEvent) {
				$event->setIdInImport($importedEvent->getIdInImport());
				$event->setImportId($importedEvent->getImportId());
			}
            // another data migration .... if no human_slug, let's add one
            if ($event->getSummary() && !$event->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $event->setSlugHuman($slugify->process($event->getSummary()));
                $stat = $this->app['db']->prepare("UPDATE event_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$event->getId(),
                    'slug_human'=>$event->getSlugHuman(),
                ));
            }
			// ... and return
			return $event;
		}
	}

	public function loadBySlug(SiteModel $site, $slug) {
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id FROM event_information ".
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
			$repo = new \repositories\ImportedEventRepository($this->app);
			$importedEvent = $repo->loadByEvent($event);
			if ($importedEvent) {
				$event->setIdInImport($importedEvent->getIdInImport());
				$event->setImportId($importedEvent->getImportId());
			}
            // another data migration .... if no human_slug, let's add one
            if ($event->getSummary() && !$event->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $event->setSlugHuman($slugify->process($event->getSummary()));
                $stat = $this->app['db']->prepare("UPDATE event_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$event->getId(),
                    'slug_human'=>$event->getSlugHuman(),
                ));
            }
			// ... and return
			return $event;
		}
	}


	public function loadBySiteIDAndEventSlug($siteid, $slug) {
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				" WHERE event_information.slug =:slug AND event_information.site_id =:sid");
		$stat->execute(array( 'sid'=>$siteid, 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$event = new EventModel();
			$event->setFromDataBaseRow($stat->fetch());
			// Now, we currently have 2 ways of linking imported events to events
			// old way - flags on event
			// new way - seperate models
			// The below code checks the new way of linking and adds it if it finds anything
			$repo = new \repositories\ImportedEventRepository($this->app);
			$importedEvent = $repo->loadByEvent($event);
			if ($importedEvent) {
				$event->setIdInImport($importedEvent->getIdInImport());
				$event->setImportId($importedEvent->getImportId());
			}
			// ... and return
			return $event;
		}
	}

	
	/**
	 * Note you can only edit undeleted events.
	 * @param EventModel $event
	 * @param UserAccountModel $creator
	 * @param EventHistoryModel $fromHistory
	 * @deprecated
	 */
	public function edit(EventModel $event,  UserAccountModel $user = null, EventHistoryModel $fromHistory = null )
	{
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		if ($fromHistory) {
			$eventEditMetaDataModel->setRevertedFromHistoryCreatedAt($fromHistory->getCreatedAt());
		}
		$this->editWithMetaData($event, $eventEditMetaDataModel);
	}

	public function editWithMetaData(EventModel $event,  EventEditMetaDataModel $eventEditMetaDataModel ) {
		if ($event->getIsDeleted()) {
			throw new \Exception("Can't edit deleted events!");
		}
		
		try {
            $this->app['db']->beginTransaction();

			$fields = array('summary','description','start_at','end_at','venue_id','area_id','country_id','timezone',
				'url','ticket_url','is_physical','is_virtual','is_deleted','is_cancelled','custom');

			$this->eventDBAccess->update($event, $fields, $eventEditMetaDataModel);
			
			
			if ($eventEditMetaDataModel->getUserAccount()) {
				if ($event->getGroupId()) {
					$ufgr = new UserWatchesGroupRepository($this->app);
					$ufgr->startUserWatchingGroupIdIfNotWatchedBefore($eventEditMetaDataModel->getUserAccount(), $event->getGroupId());
				} else {
					// TODO watch site?
				}
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}

	/**
	 * @deprecated
	 */
	public function delete(EventModel $event,  UserAccountModel $user=null) {
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		$this->deleteWithMetaData($event, $eventEditMetaDataModel);
	}

	public function deleteWithMetaData(EventModel $event,  EventEditMetaDataModel $eventEditMetaDataModel) {
		try {
            $this->app['db']->beginTransaction();

			$event->setIsDeleted(true);
			$this->eventDBAccess->update($event, array('is_deleted'), $eventEditMetaDataModel);


			// TODO if in group, watch


            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}

	/**
	 * @deprecated
	 */
	public function undelete(EventModel $event,  UserAccountModel $user=null) {
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		$this->undeleteWithMetaData($event, $eventEditMetaDataModel);
	}

	public function undeleteWithMetaData(EventModel $event,  EventEditMetaDataModel $eventEditMetaDataModel) {
		try {
            $this->app['db']->beginTransaction();

			$event->setIsDeleted(false);
			$this->eventDBAccess->update($event, array('is_deleted'), $eventEditMetaDataModel);


			// TODO if in group, watch


            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}


	/**
	 * @deprecated
	 */
	public function cancel(EventModel $event,  UserAccountModel $user=null) {
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		$this->cancelWithMetaData($event, $eventEditMetaDataModel);
	}

	public function cancelWithMetaData(EventModel $event,  EventEditMetaDataModel $eventEditMetaDataModel) {
		try {
            $this->app['db']->beginTransaction();

			$event->setIsCancelled(true);
			$this->eventDBAccess->update($event, array('is_cancelled'), $eventEditMetaDataModel);


			// TODO if in group, watch


            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}

	/**
	 * @deprecated
	 */
	public function uncancel(EventModel $event,  UserAccountModel $user=null) {
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		$this->uncancelWithMetaData($event, $eventEditMetaDataModel);
	}

	public function uncancelWithMetaData(EventModel $event,  EventEditMetaDataModel $eventEditMetaDataModel) {
		try {
            $this->app['db']->beginTransaction();

			$event->setIsCancelled(false);
			$this->eventDBAccess->update($event, array('is_cancelled'), $eventEditMetaDataModel);


			// TODO if in group, watch


            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}
	
	public function loadLastNonDeletedNonImportedByStartTimeInSiteId($siteID) {
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
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
		// We haven't got a " AND event_in_group.is_main_group = '1' " search term so the group_title & group_id returned may not be from the main group
		// but given where this is used, that's ok for now.
		// We need to make sure the search by group clause works.
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
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

    /**
     * Linking events to imported events by means of field on event_information is now old - new way is via imported_event_is_event table.
     *
     * This is left as sometimes we want to load data by old way in order to convert data from old to new.
     *
     * @deprecated
     */
    public function loadByImportURLIDAndImportId($importURLID, $importID) {
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
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
	 * @param \models\ImportedEventModel $importedEvent
	 * @return \models\EventModel
	 */
	public function loadByImportedEvent(\models\ImportedEventModel $importedEvent) {
		$stat = $this->app['db']->prepare("SELECT event_information.*, group_information.title AS group_title, group_information.id AS group_id  FROM event_information ".
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


	/**
	 * @deprecated
	 */
	public function moveAllFutureEventsAtVenueToNoSetVenue(VenueModel $venue, UserAccountModel $user) {
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		$this->moveAllFutureEventsAtVenueToNoSetVenueWithMetaData($venue, $eventEditMetaDataModel);
	}

	public function moveAllFutureEventsAtVenueToNoSetVenueWithMetaData(VenueModel $venue, EventEditMetaDataModel $eventEditMetaDataModel) {

			$statFetch = $this->app['db']->prepare("SELECT event_information.* FROM event_information WHERE venue_id = :venue_id AND start_at > :start_at AND is_deleted='0'");
			$statFetch->execute(array('venue_id'=>$venue->getId(), 'start_at'=>$this->app['timesource']->getFormattedForDataBase()));
			while($data = $statFetch->fetch()) {
				$event = new EventModel();
				$event->setFromDataBaseRow($data);
				$event->setVenueId(null);
				$event->setAreaId($venue->getAreaId());

				$this->eventDBAccess->update($event, array('venue_id','area_id'), $eventEditMetaDataModel);
			}
		
	}
	
	public function loadEventJustBeforeEdit(EventModel $event, EventHistoryModel $eventHistory) {
		$eventOut = clone $event;
		
		/**
		 * At moment the last edit has all state, changed or not.
		 * 
		 * In future we may only store state in event_history for changed fields to save space.
		 * 
		 * When we do that, we'll have to iterate back over multiple event_histories until we have all fields.
		 */
		$stat = $this->app['db']->prepare("SELECT event_history.* FROM event_history ".
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

	/**
	 * @deprecated
	 */
	public function markDuplicate(EventModel $duplicateEvent, EventModel $originalEvent, UserAccountModel $user=null) {
		$eventEditMetaDataModel = new EventEditMetaDataModel();
		$eventEditMetaDataModel->setUserAccount($user);
		$this->markDuplicateWithMetaData($duplicateEvent, $originalEvent, $eventEditMetaDataModel);
	}

	public function markDuplicateWithMetaData(EventModel $duplicateEvent, EventModel $originalEvent, EventEditMetaDataModel $eventEditMetaDataModel) {

		if ($duplicateEvent->getId() == $originalEvent->getId()) return;


		try {
            $this->app['db']->beginTransaction();

			$duplicateEvent->setIsDeleted(true);
			$duplicateEvent->setIsDuplicateOfId($originalEvent->getId());
			$this->eventDBAccess->update($duplicateEvent, array('is_deleted','is_duplicate_of_id'), $eventEditMetaDataModel);

			$uaeRepo = new UserAtEventRepository($this->app);
			$uaerb = new UserAtEventRepositoryBuilder($this->app);
			$uaerb->setEventOnly($duplicateEvent);
			foreach($uaerb->fetchAll() as $uaeDuplicate) {
				if ($uaeDuplicate->getIsPlanAttending() || $uaeDuplicate->getIsPlanMaybeAttending()) {
					$uaeOriginal = $uaeRepo->loadByUserIDAndEventOrInstanciate($uaeDuplicate->getUserAccountId(), $originalEvent);
					// does user already have plans for this event? If so leave them.
					if (!$uaeOriginal->getIsPlanAttending() && !$uaeOriginal->getIsPlanMaybeAttending()) {
						$uaeOriginal->setIsPlanAttending($uaeDuplicate->getIsPlanAttending());
						$uaeOriginal->setIsPlanMaybeAttending($uaeDuplicate->getIsPlanMaybeAttending());
						$uaeOriginal->setIsPlanPublic($uaeDuplicate->getIsPlanPublic());
						$uaeRepo->save($uaeOriginal);
					}
				}
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventSaved', array('event_id'=>$duplicateEvent->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}


	}

	public function markNotDuplicateWithMetaData(EventModel $notDuplicateEvent, EventEditMetaDataModel $eventEditMetaDataModel) {
		$notDuplicateEvent->setIsDuplicateOfId(null);
		$this->eventDBAccess->update($notDuplicateEvent, array('is_duplicate_of_id'), $eventEditMetaDataModel);

	}

	public function purge(EventModel $event) {
		try {
            $this->app['db']->beginTransaction();
			
			
			$stat = $this->app['db']->prepare("UPDATE event_history SET is_duplicate_of_id=NULL, is_duplicate_of_id_changed=0 WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$event->getId()));

			$stat = $this->app['db']->prepare("UPDATE event_information SET is_duplicate_of_id=NULL WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$event->getId()));
			
			$stat = $this->app['db']->prepare("DELETE FROM event_in_group WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM user_at_event_information WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM event_in_curated_list WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));
			
			$stat = $this->app['db']->prepare("DELETE FROM event_has_tag WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM event_history WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM media_in_event WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));

			$statDeleteComment = $this->app['db']->prepare("DELETE FROM sysadmin_comment_information WHERE id=:id");
			$statDeleteLink = $this->app['db']->prepare("DELETE FROM sysadmin_comment_about_event WHERE sysadmin_comment_id=:id");
			$stat = $this->app['db']->prepare("SELECT sysadmin_comment_id FROM sysadmin_comment_about_event WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));
			while($data = $stat->fetch()) {
				$statDeleteLink->execute(array($data['sysadmin_comment_id']));
				$statDeleteComment->execute(array($data['sysadmin_comment_id']));
			}

			$statDeleteInfo = $this->app['db']->prepare("DELETE FROM new_event_draft_information WHERE id=:id");
			$statDeleteHistory = $this->app['db']->prepare("DELETE FROM new_event_draft_history WHERE new_event_draft_id=:id");
			$stat = $this->app['db']->prepare("SELECT id FROM new_event_draft_information WHERE event_id=:id");
			$stat->execute(array('id'=>$event->getId()));
			while($data = $stat->fetch()) {
				$statDeleteHistory->execute(array($data['id']));
				$statDeleteInfo->execute(array($data['id']));
			}

			$stat = $this->app['db']->prepare("DELETE FROM event_information WHERE id=:id");
			$stat->execute(array('id'=>$event->getId()));

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventPurged', array());
		} catch (Exception $e) {
            $this->app['db']->rollBack();
			throw $e;
		}
	}
	
}

