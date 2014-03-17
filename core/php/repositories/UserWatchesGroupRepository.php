<?php

namespace repositories;

use models\UserAccountModel;
use models\GroupModel;
use models\UserWatchesGroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupRepository {

	public function loadByUserAndGroup(UserAccountModel $user, GroupModel $group) {
		return $this->loadByUserAndGroupId($user, $group->getId());
	}
	
	public function loadByUserAndGroupId(UserAccountModel $user, $groupID) {
		global $DB;
		$stat = $DB->prepare("SELECT user_watches_group_information.* FROM user_watches_group_information WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'group_id'=>$groupID ));
		if ($stat->rowCount() > 0) {
			$uws = new UserWatchesGroupModel();
			$uws->setFromDataBaseRow($stat->fetch());
			return $uws;
		}		
	}
	
	/**
	 * Note this does not check if user is watching site first! TODO?
	 */
	public function startUserWatchingGroup(UserAccountModel $user, GroupModel $group) {
		global $DB;
	
		$uws = $this->loadByUserAndGroup($user, $group);
		if ($uws && $uws->getIsWatching()) {
			// all done!
		} else if ($uws && !$uws->getIsWatching()) {
			$stat = $DB->prepare("UPDATE user_watches_group_information SET is_watching='1',last_watch_started=:last_watch_started WHERE user_account_id =:user_account_id AND group_id=:group_id");
			$stat->execute(array( 'user_account_id'=>$user->getId(), 'group_id'=>$group->getId(), 'last_watch_started'=> \TimeSource::getFormattedForDataBase()));
		} else {
			$stat = $DB->prepare("INSERT INTO user_watches_group_information (user_account_id,group_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:group_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'group_id'=>$group->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  \TimeSource::getFormattedForDataBase(),
					'last_watch_started'=>  \TimeSource::getFormattedForDataBase(),
				));			
		}
		
	}
	
	public function stopUserWatchingGroup(UserAccountModel $user, GroupModel $group) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_watches_group_information SET is_watching='0' WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'group_id'=>$group->getId() ));
	}

		
	
	public function markNotifyEmailSent(UserWatchesGroupModel $userWatchesGroup, $emailTime) {
		global $DB;
		// new way
		$stat = $DB->prepare("INSERT INTO user_watches_group_notify_email (user_account_id,group_id,sent_at) VALUES (:user_account_id,:group_id,:sent_at)");
		$stat->execute(array( 'user_account_id'=>$userWatchesGroup->getUserAccountId(), 'group_id'=>$userWatchesGroup->getGroupId(), 'sent_at'=>$emailTime->format("Y-m-d H:i:s") ));		
		// old way. At some point when have enought new data remove this and just use new way
		$stat = $DB->prepare("UPDATE user_watches_group_information SET last_notify_email_sent=:sent WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesGroup->getUserAccountId(), 'group_id'=>$userWatchesGroup->getGroupId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
	
	public function markPromptEmailSent(UserWatchesGroupModel $userWatchesGroup, $emailTime) {
		global $DB;
		// new way
		$stat = $DB->prepare("INSERT INTO user_watches_group_prompt_email (user_account_id,group_id,sent_at) VALUES (:user_account_id,:group_id,:sent_at)");
		$stat->execute(array( 'user_account_id'=>$userWatchesGroup->getUserAccountId(), 'group_id'=>$userWatchesGroup->getGroupId(), 'sent_at'=>$emailTime->format("Y-m-d H:i:s") ));		
		// old way. At some point when have enought new data remove this and just use new way
		$stat = $DB->prepare("UPDATE user_watches_group_information SET last_prompt_email_sent=:sent WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesGroup->getUserAccountId(), 'group_id'=>$userWatchesGroup->getGroupId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
		
	/*
	 * Note this does not check if user is watching site first! TODO?
	 */
	public function startUserWatchingGroupIfNotWatchedBefore(UserAccountModel $user, GroupModel $group) {
		$this->startUserWatchingGroupIdIfNotWatchedBefore($user, $group->getId());
	}
	
	/*
	 * Note this does not check if user is watching site first! TODO?
	 */	
	public function startUserWatchingGroupIdIfNotWatchedBefore(UserAccountModel $user, $groupID) {
		global $DB;
		$uws = $this->loadByUserAndGroupId($user, $groupID);
		if ($uws) {
			// all done! They are already watching or they once were watching.
		} else {
			$stat = $DB->prepare("INSERT INTO user_watches_group_information (user_account_id,group_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:group_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'group_id'=>$groupID,
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  \TimeSource::getFormattedForDataBase(),
					'last_watch_started'=>  \TimeSource::getFormattedForDataBase(),
				));			
		}
		
	}
	
}

