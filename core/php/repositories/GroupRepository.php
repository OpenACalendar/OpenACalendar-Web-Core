<?php


namespace repositories;

use dbaccess\GroupDBAccess;
use models\GroupModel;
use models\EventModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserWatchesGroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupRepository {

	/** @var  \dbaccess\GroupDBAccess */
	protected $groupDBAccess;

	function __construct()
	{
		global $DB, $USERAGENT;
		$this->groupDBAccess = new GroupDBAccess($DB, new \TimeSource(), $USERAGENT);
	}

	
	public function create(GroupModel $group, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM group_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$group->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO group_information (site_id, slug, title,url,description,created_at,twitter_username,approved_at) ".
					"VALUES (:site_id, :slug, :title, :url, :description, :created_at, :twitter_username,:approved_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$group->getSlug(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$group->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO group_history (group_id, title, url, description, user_account_id  , created_at, approved_at, twitter_username, is_new) VALUES ".
					"(:group_id, :title, :url, :description, :user_account_id  , :created_at, :approved_at, :twitter_username, '1')");
			$stat->execute(array(
					'group_id'=>$group->getId(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			
			$ufgr = new UserWatchesGroupRepository();
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($creator, $group);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT group_information.* FROM group_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$group = new GroupModel();
			$group->setFromDataBaseRow($stat->fetch());
			return $group;
		}
	}
	
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT group_information.* FROM group_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id, ));
		if ($stat->rowCount() > 0) {
			$group = new GroupModel();
			$group->setFromDataBaseRow($stat->fetch());
			return $group;
		}
	}
	
	public function edit(GroupModel $group, UserAccountModel $user) {
		global $DB;
		if ($group->getIsDeleted()) {
			throw new \Exception("Can't edit deleted group!");
		}
		try {
			$DB->beginTransaction();

			$fields = array('title','url','twitter_username','description');
			$this->groupDBAccess->update($group, $fields, $user);

			$ufgr = new UserWatchesGroupRepository();
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($user, $group);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function delete(GroupModel $group, UserAccountModel $user) {
		global $DB;
		try {
			$DB->beginTransaction();


			$group->setIsDeleted(true);
			$this->groupDBAccess->update($group, array('is_deleted'), $user);

			$ufgr = new UserWatchesGroupRepository();
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($user, $group);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function addEventToGroup(EventModel $event, GroupModel $group, UserAccountModel $user=null) {
		global $DB;
		
		// check event not already in list
		$stat = $DB->prepare("SELECT * FROM event_in_group WHERE group_id=:group_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
		$stat->execute(array(
			'group_id'=>$group->getId(),
			'event_id'=>$event->getId(),
		));
		if ($stat->rowCount() > 0) {
			return;
		}
		
		try {
			$DB->beginTransaction();
			
			// now, do we need to make this the main group?
			$stat = $DB->prepare("SELECT * FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL AND is_main_group = '1'");
			$stat->execute(array(
				'event_id'=>$event->getId(),
			));
			$isMainGroup =  ($stat->rowCount() == 0);
			
			
			// Add!
			$stat = $DB->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,addition_approved_at,is_main_group) ".
					"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at,:is_main_group)");
			$stat->execute(array(
				'group_id'=>$group->getId(),
				'event_id'=>$event->getId(),
				'is_main_group'=>$isMainGroup?1:0,
				'added_by_user_account_id'=>($user?$user->getId():null),
				'added_at'=>  \TimeSource::getFormattedForDataBase(),
				'addition_approved_at'=>  \TimeSource::getFormattedForDataBase(),
			));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
		
	}


	public function removeEventFromGroup(EventModel $event, GroupModel $group, UserAccountModel $user=null) {
		global $DB;
		try {
			$DB->beginTransaction();
			
			$stat = $DB->prepare("UPDATE event_in_group SET removed_by_user_account_id=:removed_by_user_account_id,".
					" removed_at=:removed_at, removal_approved_at=:removal_approved_at WHERE ".
					" event_id=:event_id AND group_id=:group_id AND removed_at IS NULL ");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'group_id'=>$group->getId(),
					'removed_at'=>  \TimeSource::getFormattedForDataBase(),
					'removal_approved_at'=>  \TimeSource::getFormattedForDataBase(),
					'removed_by_user_account_id'=>($user?$user->getId():null),
			));
			
			// now, do we need to make something else the main group?
			// are there other groups?
			$stat = $DB->prepare("SELECT * FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL");
			$stat->execute(array(
				'event_id'=>$event->getId(),
			));
			if ($stat->rowCount() > 0) {
				// do we have no main group set?
				$stat = $DB->prepare("SELECT * FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL AND is_main_group = '1'");
				$stat->execute(array(
					'event_id'=>$event->getId(),
				));
				if ($stat->rowCount() == 0) {
					// let's set a main group!
					$stat = $DB->prepare("UPDATE event_in_group SET is_main_group='1' WHERE event_id=:event_id AND removed_at IS NULL ".
							"AND group_id = (SELECT group_id FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL LIMIT 1)");
					$stat->execute(array(
						'event_id'=>$event->getId(),
					));
				}
			}
		
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function setMainGroupForEvent(GroupModel $group, EventModel $event, UserAccountModel $user=null) {
		global $DB;
		try {
			$DB->beginTransaction();
		
			// check group in event first
			$stat = $DB->prepare("SELECT * FROM event_in_group WHERE group_id=:group_id AND ".
					" event_id=:event_id AND removed_at IS NULL ");
			$stat->execute(array(
				'group_id'=>$group->getId(),
				'event_id'=>$event->getId(),
			));
			if ($stat->rowCount() > 0) {

				// set main group
				$stat = $DB->prepare("UPDATE event_in_group SET is_main_group='1' WHERE event_id=:event_id AND removed_at IS NULL ".
							"AND group_id = :group_id");
				$stat->execute(array(
						'event_id'=>$event->getId(),
						'group_id'=>$group->getId(),
					));
				
				// remove others
				$stat = $DB->prepare("UPDATE event_in_group SET is_main_group='0' WHERE event_id=:event_id AND removed_at IS NULL ".
							"AND group_id != :group_id");
				$stat->execute(array(
						'event_id'=>$event->getId(),
						'group_id'=>$group->getId(),
					));
				
			}
		
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
		
	}
	/**
	 * 
	 * @return int|boolean  0= false, 1=warn, 2=out
	 */
	public function isGroupRunningOutOfFutureEvents(GroupModel $group, SiteModel $site) {
		global $DB, $CONFIG;
		
		if (!$group) return 0;
		
		$stat = $DB->prepare("SELECT event_information.start_at FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL ".
				"WHERE event_in_group.group_id =:id AND start_at > :start_at AND is_deleted = '0' ".
				"ORDER BY event_information.start_at DESC");
		$stat->execute(array( 
			'id'=>$group->getId(), 
			'start_at'=>  \TimeSource::getFormattedForDataBase(),
			));
		if ($stat->rowCount() > 0) {
			$data = $stat->fetch();
			$utc = new \DateTimeZone("UTC");
			$lastStartAt = new \DateTime($data['start_at'], $utc);
			
			$secondsToWarn = $site->getPromptEmailsDaysInAdvance() * 24 * 60 * 60;
			if ($lastStartAt->getTimestamp() < \TimeSource::time() + $secondsToWarn) {
				return 1;
			} else {
				return 0;
			}
		}
		
		return 2;
	}

	public function markDuplicate(GroupModel $duplicateGroup, GroupModel $originalGroup, UserAccountModel $user=null) {
		global $DB;

		if ($duplicateGroup->getId() == $originalGroup->getId()) return;

		try {
			$DB->beginTransaction();

			$duplicateGroup->setIsDeleted(true);
			$duplicateGroup->setIsDuplicateOfId($originalGroup->getId());
			$this->groupDBAccess->update($duplicateGroup, array('is_deleted','is_duplicate_of_id'), $user);

			// Users Watching Group
			$ufgr = new UserWatchesGroupRepository();
			$usersRepo = new UserAccountRepositoryBuilder();
			$usersRepo->setWatchesGroup($duplicateGroup);
			foreach($usersRepo->fetchAll() as $user) {
				$ufgr->startUserWatchingGroupIfNotWatchedBefore($user, $originalGroup);
			}

			// Events in Group
			$statCheck = $DB->prepare("SELECT * FROM event_in_group WHERE group_id=:group_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
			$statAdd = $DB->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,addition_approved_at,is_main_group) ".
				"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at,:is_main_group)");
			$erb = new EventRepositoryBuilder();
			$erb->setGroup($duplicateGroup);
			foreach($erb->fetchAll() as $event) {
				// check event not already in list
				$statCheck->execute(array(
					'group_id'=>$originalGroup->getId(),
					'event_id'=>$event->getId(),
				));
				if ($statCheck->rowCount() == 0) {
					// TODO is_main_group ??????????????????
					$statAdd->execute(array(
						'group_id'=>$originalGroup->getId(),
						'event_id'=>$event->getId(),
						'is_main_group'=>0,
						'added_by_user_account_id'=>($user?$user->getId():null),
						'added_at'=>  \TimeSource::getFormattedForDataBase(),
						'addition_approved_at'=>  \TimeSource::getFormattedForDataBase(),
					));
				}
			}

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}



	public function purge(GroupModel $group) {
		global $DB;
		try {
			$DB->beginTransaction();
			
			$stat = $DB->prepare("DELETE FROM user_watches_group_notify_email WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM user_watches_group_prompt_email WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM user_watches_group_stop WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM user_watches_group_information WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM user_watches_group_notify_email WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM event_in_group WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM group_history WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $DB->prepare("DELETE FROM group_information WHERE id=:id");
			$stat->execute(array('id'=>$group->getId()));
		
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
			throw $e;
		}
	}
	
}

