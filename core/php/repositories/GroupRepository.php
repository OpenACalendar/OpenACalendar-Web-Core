<?php


namespace repositories;

use dbaccess\GroupDBAccess;
use models\GroupEditMetaDataModel;
use models\GroupModel;
use models\EventModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserWatchesGroupRepository;
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
class GroupRepository {

    /** @var Application */
    private  $app;

	/** @var  \dbaccess\GroupDBAccess */
	protected $groupDBAccess;

	function __construct(Application $app)
	{
        $this->app = $app;
		$this->groupDBAccess = new GroupDBAccess($app);
	}

	
	public function create(GroupModel $group, SiteModel $site, UserAccountModel $creator) {
        $slugify = new Slugify($this->app);
		$this->app['extensionhookrunner']->beforeGroupSave($group,$creator);

		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM group_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$group->setSlug($data['c'] + 1);
			
			$stat = $this->app['db']->prepare("INSERT INTO group_information (site_id, slug, slug_human,  title,url,description,created_at,twitter_username,approved_at) ".
					"VALUES (:site_id, :slug, :slug_human,  :title, :url, :description, :created_at, :twitter_username,:approved_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$group->getSlug(),
                    'slug_human'=>$slugify->process($group->getTitle()),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$group->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO group_history (group_id, title, url, description, user_account_id  , created_at, approved_at, twitter_username, is_new) VALUES ".
					"(:group_id, :title, :url, :description, :user_account_id  , :created_at, :approved_at, :twitter_username, '1')");
			$stat->execute(array(
					'group_id'=>$group->getId(),
					'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$group->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			
			$ufgr = new UserWatchesGroupRepository($this->app);
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($creator, $group);

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'GroupSaved', array('group_id'=>$group->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		$stat = $this->app['db']->prepare("SELECT group_information.* FROM group_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$group = new GroupModel();
			$group->setFromDataBaseRow($stat->fetch());
            //  data migration .... if no human_slug, let's add one
            if ($group->getTitle() && !$group->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $group->setSlugHuman($slugify->process($group->getTitle()));
                $stat = $this->app['db']->prepare("UPDATE group_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$group->getId(),
                    'slug_human'=>$group->getSlugHuman(),
                ));
            }
			return $group;
		}
	}
	
	
	public function loadById($id) {
		$stat = $this->app['db']->prepare("SELECT group_information.* FROM group_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id, ));
		if ($stat->rowCount() > 0) {
			$group = new GroupModel();
			$group->setFromDataBaseRow($stat->fetch());
            //  data migration .... if no human_slug, let's add one
            if ($group->getTitle() && !$group->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $group->setSlugHuman($slugify->process($group->getTitle()));
                $stat = $this->app['db']->prepare("UPDATE group_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$group->getId(),
                    'slug_human'=>$group->getSlugHuman(),
                ));
            }
			return $group;
		}
	}

	/*
	* @deprecated
	*/
	public function edit(GroupModel $group, UserAccountModel $user) {
		$groupEditMetaDataModel = new GroupEditMetaDataModel();
		$groupEditMetaDataModel->setUserAccount($user);
		$this->editWithMetaData($group, $groupEditMetaDataModel);
	}

	public function editWithMetaData(GroupModel $group, GroupEditMetaDataModel $groupEditMetaDataModel) {
		if ($group->getIsDeleted()) {
			throw new \Exception("Can't edit deleted group!");
		}

		$this->app['extensionhookrunner']->beforeGroupSave($group,$groupEditMetaDataModel->getUserAccount());

		try {
            $this->app['db']->beginTransaction();

			$fields = array('title','url','twitter_username','description');
			$this->groupDBAccess->update($group, $fields, $groupEditMetaDataModel);

			$ufgr = new UserWatchesGroupRepository($this->app);
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($groupEditMetaDataModel->getUserAccount(), $group);

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'GroupSaved', array('group_id'=>$group->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}


	/*
	* @deprecated
	*/
	public function delete(GroupModel $group, UserAccountModel $user) {
		$groupEditMetaDataModel = new GroupEditMetaDataModel();
		$groupEditMetaDataModel->setUserAccount($user);
		$this->deleteWithMetaData($group, $groupEditMetaDataModel);
	}

	public function deleteWithMetaData(GroupModel $group,  GroupEditMetaDataModel $groupEditMetaDataModel) {

		$this->app['extensionhookrunner']->beforeGroupSave($group,$groupEditMetaDataModel->getUserAccount());

		try {
            $this->app['db']->beginTransaction();


			$group->setIsDeleted(true);
			$this->groupDBAccess->update($group, array('is_deleted'), $groupEditMetaDataModel);

			$ufgr = new UserWatchesGroupRepository($this->app);
			$ufgr->startUserWatchingGroupIfNotWatchedBefore($groupEditMetaDataModel->getUserAccount(), $group);

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'GroupSaved', array('group_id'=>$group->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}

	/*
	* @deprecated
	*/
	public function undelete(GroupModel $group, UserAccountModel $user) {
		$groupEditMetaDataModel = new GroupEditMetaDataModel();
		$groupEditMetaDataModel->setUserAccount($user);
		$this->undeleteWithMetaData($group, $groupEditMetaDataModel);
	}

	public function undeleteWithMetaData(GroupModel $group,  GroupEditMetaDataModel $groupEditMetaDataModel) {

		$this->app['extensionhookrunner']->beforeGroupSave($group,$groupEditMetaDataModel->getUserAccount());

		try {
            $this->app['db']->beginTransaction();


			$group->setIsDeleted(false);
			$this->groupDBAccess->update($group, array('is_deleted'), $groupEditMetaDataModel);

			if ($groupEditMetaDataModel->getUserAccount()) {
				$ufgr = new UserWatchesGroupRepository($this->app);
				$ufgr->startUserWatchingGroupIfNotWatchedBefore($groupEditMetaDataModel->getUserAccount(), $group);
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'GroupSaved', array('group_id'=>$group->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}

	
	public function addEventToGroup(EventModel $event, GroupModel $group, UserAccountModel $user=null) {

		// check event not already in list
		$stat = $this->app['db']->prepare("SELECT * FROM event_in_group WHERE group_id=:group_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
		$stat->execute(array(
			'group_id'=>$group->getId(),
			'event_id'=>$event->getId(),
		));
		if ($stat->rowCount() > 0) {
			return;
		}
		
		try {
            $this->app['db']->beginTransaction();
			
			// now, do we need to make this the main group?
			$stat = $this->app['db']->prepare("SELECT * FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL AND is_main_group = '1'");
			$stat->execute(array(
				'event_id'=>$event->getId(),
			));
			$isMainGroup =  ($stat->rowCount() == 0);
			
			
			// Add!
			$stat = $this->app['db']->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,addition_approved_at,is_main_group) ".
					"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at,:is_main_group)");
			$stat->execute(array(
				'group_id'=>$group->getId(),
				'event_id'=>$event->getId(),
				'is_main_group'=>$isMainGroup?1:0,
				'added_by_user_account_id'=>($user?$user->getId():null),
				'added_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'addition_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
			));

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventInGroupSaved', array('group_id'=>$group->getId(),'event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
		
	}


	public function removeEventFromGroup(EventModel $event, GroupModel $group, UserAccountModel $user=null) {
		try {
            $this->app['db']->beginTransaction();
			
			$stat = $this->app['db']->prepare("UPDATE event_in_group SET removed_by_user_account_id=:removed_by_user_account_id,".
					" removed_at=:removed_at, removal_approved_at=:removal_approved_at WHERE ".
					" event_id=:event_id AND group_id=:group_id AND removed_at IS NULL ");
			$stat->execute(array(
					'event_id'=>$event->getId(),
					'group_id'=>$group->getId(),
					'removed_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'removal_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'removed_by_user_account_id'=>($user?$user->getId():null),
			));
			
			// now, do we need to make something else the main group?
			// are there other groups?
			$stat = $this->app['db']->prepare("SELECT * FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL");
			$stat->execute(array(
				'event_id'=>$event->getId(),
			));
			if ($stat->rowCount() > 0) {
				// do we have no main group set?
				$stat = $this->app['db']->prepare("SELECT * FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL AND is_main_group = '1'");
				$stat->execute(array(
					'event_id'=>$event->getId(),
				));
				if ($stat->rowCount() == 0) {
					// let's set a main group!
					$stat = $this->app['db']->prepare("UPDATE event_in_group SET is_main_group='1' WHERE event_id=:event_id AND removed_at IS NULL ".
							"AND group_id = (SELECT group_id FROM event_in_group WHERE  event_id=:event_id AND removed_at IS NULL LIMIT 1)");
					$stat->execute(array(
						'event_id'=>$event->getId(),
					));
				}
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventInGroupSaved', array('group_id'=>$group->getId(),'event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}
	
	public function setMainGroupForEvent(GroupModel $group, EventModel $event, UserAccountModel $user=null) {
		try {
            $this->app['db']->beginTransaction();
		
			// check group in event first
			$stat = $this->app['db']->prepare("SELECT * FROM event_in_group WHERE group_id=:group_id AND ".
					" event_id=:event_id AND removed_at IS NULL ");
			$stat->execute(array(
				'group_id'=>$group->getId(),
				'event_id'=>$event->getId(),
			));
			if ($stat->rowCount() > 0) {

				// set main group
				$stat = $this->app['db']->prepare("UPDATE event_in_group SET is_main_group='1' WHERE event_id=:event_id AND removed_at IS NULL ".
							"AND group_id = :group_id");
				$stat->execute(array(
						'event_id'=>$event->getId(),
						'group_id'=>$group->getId(),
					));
				
				// remove others
				$stat = $this->app['db']->prepare("UPDATE event_in_group SET is_main_group='0' WHERE event_id=:event_id AND removed_at IS NULL ".
							"AND group_id != :group_id");
				$stat->execute(array(
						'event_id'=>$event->getId(),
						'group_id'=>$group->getId(),
					));
				
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventInGroupSaved', array('group_id'=>$group->getId(),'event_id'=>$event->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
		
	}
	/**
	 * 
	 * @return int|boolean  0= false, 1=warn, 2=out
	 */
	public function isGroupRunningOutOfFutureEvents(GroupModel $group, SiteModel $site) {

		if (!$group) return 0;
		
		$stat = $this->app['db']->prepare("SELECT event_information.start_at FROM event_information ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL ".
				"WHERE event_in_group.group_id =:id AND start_at > :start_at AND is_deleted = '0' ".
				"ORDER BY event_information.start_at DESC");
		$stat->execute(array( 
			'id'=>$group->getId(), 
			'start_at'=>  $this->app['timesource']->getFormattedForDataBase(),
			));
		if ($stat->rowCount() > 0) {
			$data = $stat->fetch();
			$utc = new \DateTimeZone("UTC");
			$lastStartAt = new \DateTime($data['start_at'], $utc);
			
			$secondsToWarn = $site->getPromptEmailsDaysInAdvance() * 24 * 60 * 60;
			if ($lastStartAt->getTimestamp() < $this->app['timesource']->time() + $secondsToWarn) {
				return 1;
			} else {
				return 0;
			}
		}
		
		return 2;
	}

	/*
	* @deprecated
	*/
	public function markDuplicate(GroupModel $duplicateGroup, GroupModel $originalGroup, UserAccountModel $user=null) {
		$groupEditMetaDataModel = new GroupEditMetaDataModel();
		$groupEditMetaDataModel->setUserAccount($user);
		$this->markDuplicateWithMetaData($duplicateGroup, $originalGroup, $groupEditMetaDataModel);
	}

	public function markDuplicateWithMetaData(GroupModel $duplicateGroup, GroupModel $originalGroup, GroupEditMetaDataModel $groupEditMetaDataModel) {

		if ($duplicateGroup->getId() == $originalGroup->getId()) return;

		try {
            $this->app['db']->beginTransaction();

			$duplicateGroup->setIsDeleted(true);
			$duplicateGroup->setIsDuplicateOfId($originalGroup->getId());
			$this->groupDBAccess->update($duplicateGroup, array('is_deleted','is_duplicate_of_id'), $groupEditMetaDataModel);

			// Users Watching Group
			$ufgr = new UserWatchesGroupRepository($this->app);
			$usersRepo = new UserAccountRepositoryBuilder($this->app);
			$usersRepo->setWatchesGroup($duplicateGroup);
			foreach($usersRepo->fetchAll() as $user) {
				$ufgr->startUserWatchingGroupIfNotWatchedBefore($user, $originalGroup);
			}

			// Events in Group
			$statCheck = $this->app['db']->prepare("SELECT * FROM event_in_group WHERE group_id=:group_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
			$statAdd = $this->app['db']->prepare("INSERT INTO event_in_group (group_id,event_id,added_by_user_account_id,added_at,addition_approved_at,is_main_group) ".
				"VALUES (:group_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at,:is_main_group)");
			$erb = new EventRepositoryBuilder($this->app);
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
						'added_at'=>  $this->app['timesource']->getFormattedForDataBase(),
						'addition_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					));
				}
			}

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'GroupSaved', array('group_id'=>$duplicateGroup->getId()));
		} catch (Exception $e) {
            $this->app['db']->rollBack();
		}
	}



	public function purge(GroupModel $group) {
		try {
            $this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("UPDATE group_history SET is_duplicate_of_id=NULL, is_duplicate_of_id_changed=0 WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $this->app['db']->prepare("UPDATE group_information SET is_duplicate_of_id=NULL WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM user_watches_group_stop WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM user_watches_group_information WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM event_in_group WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM group_history WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));

			$statDeleteComment = $this->app['db']->prepare("DELETE FROM sysadmin_comment_information WHERE id=:id");
			$statDeleteLink = $this->app['db']->prepare("DELETE FROM sysadmin_comment_about_group WHERE sysadmin_comment_id=:id");
			$stat = $this->app['db']->prepare("SELECT sysadmin_comment_id FROM sysadmin_comment_about_group WHERE group_id=:id");
			$stat->execute(array('id'=>$group->getId()));
			while($data = $stat->fetch()) {
				$statDeleteLink->execute(array($data['sysadmin_comment_id']));
				$statDeleteComment->execute(array($data['sysadmin_comment_id']));
			}
			
			$stat = $this->app['db']->prepare("DELETE FROM group_information WHERE id=:id");
			$stat->execute(array('id'=>$group->getId()));

            $this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'GroupPurged', array());
		} catch (Exception $e) {
            $this->app['db']->rollBack();
			throw $e;
		}
	}

    public function updateFutureEventsCache(GroupModel $group) {
        $statUpdate = $this->app['db']->prepare("UPDATE group_information SET cached_future_events=:count WHERE id=:id");

        $erb = new EventRepositoryBuilder($this->app);
        $erb->setGroup($group);
        $erb->setIncludeDeleted(false);
        $erb->setIncludeCancelled(false);
        $erb->setAfterNow();
        $count = count($erb->fetchAll());

        $statUpdate->execute(array('count'=>$count,'id'=>$group->getId()));

        $group->setCachedFutureEvents($count);
    }

}

