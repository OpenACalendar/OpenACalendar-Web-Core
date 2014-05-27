<?php


namespace repositories;

use models\GroupModel;
use models\EventModel;
use models\SiteModel;
use models\UserAccountModel;
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
	
	
	public function create(GroupModel $group, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM group_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$group->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO group_information (site_id, slug, title,url,description,created_at,twitter_username) ".
					"VALUES (:site_id, :slug, :title, :url, :description, :created_at, :twitter_username) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$group->getSlug(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'created_at'=>\TimeSource::getFormattedForDataBase()
				));
			$data = $stat->fetch();
			$group->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO group_history (group_id, title, url, description, user_account_id  , created_at, twitter_username, is_new) VALUES ".
					"(:group_id, :title, :url, :description, :user_account_id  , :created_at, :twitter_username, '1')");
			$stat->execute(array(
					'group_id'=>$group->getId(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
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
	
	public function edit(GroupModel $group, UserAccountModel $creator) {
		global $DB;
		if ($group->getIsDeleted()) {
			throw new \Exception("Can't edit deleted group!");
		}
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE group_information  SET title=:title, url=:url, description=:description, twitter_username=:twitter_username, is_deleted='0' WHERE id=:id");
			$stat->execute(array(
					'id'=>$group->getId(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
				));
			
			$stat = $DB->prepare("INSERT INTO group_history (group_id, title, url, description, user_account_id  , created_at, twitter_username) VALUES ".
					"(:group_id, :title, :url, :description,  :user_account_id  , :created_at, :twitter_username)");
			$stat->execute(array(
					'group_id'=>$group->getId(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'user_account_id'=>$creator->getId(),	
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),			
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			

			$ufgr = new UserWatchesGroupRepository();
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($creator, $group);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function delete(GroupModel $group, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE group_information  SET is_deleted='1' WHERE id=:id");
			$stat->execute(array(
					'id'=>$group->getId(),
				));
			
			$stat = $DB->prepare("INSERT INTO group_history (group_id, title, url, description, user_account_id  , created_at, twitter_username, is_deleted) VALUES ".
					"(:group_id, :title, :url, :description,  :user_account_id  , :created_at, :twitter_username, '1')");
			$stat->execute(array(
					'group_id'=>$group->getId(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'user_account_id'=>$creator->getId(),	
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),			
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			

			$ufgr = new UserWatchesGroupRepository();
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($creator, $group);
			
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
			$stat = $DB->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,is_main_group) ".
					"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:is_main_group)");
			$stat->execute(array(
				'group_id'=>$group->getId(),
				'event_id'=>$event->getId(),
				'is_main_group'=>$isMainGroup?1:0,
				'added_by_user_account_id'=>($user?$user->getId():null),
				'added_at'=>  \TimeSource::getFormattedForDataBase(),
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
					" removed_at=:removed_at WHERE ".
					" event_id=:event_id AND group_id=:group_id AND removed_at IS NULL ");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'group_id'=>$group->getId(),
					'removed_at'=>  \TimeSource::getFormattedForDataBase(),
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
	
}

