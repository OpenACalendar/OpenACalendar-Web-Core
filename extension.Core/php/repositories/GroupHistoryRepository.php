<?php

namespace repositories;

use models\GroupModel;
use models\GroupHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupHistoryRepository {

	
	public function ensureChangedFlagsAreSet(GroupHistoryModel $grouphistory) {
		global $DB;
		
		// do we already have them?
		if (!$grouphistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM group_history WHERE group_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$grouphistory->getId(),'at'=>$grouphistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$grouphistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new GroupHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$grouphistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE group_history SET ".
				" is_new = :is_new, ".
				" title_changed = :title_changed,  ".
				" description_changed = :description_changed,  ".
				" url_changed = :url_changed,  ".
				" twitter_username_changed = :twitter_username_changed,  ".
				" is_deleted_changed = :is_deleted_changed  ".
				"WHERE group_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$grouphistory->getId(),
				'created_at'=>$grouphistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'is_new'=>$grouphistory->getIsNew()?1:0,
				'title_changed'=> $grouphistory->getTitleChanged() ? 1 : -1,
				'description_changed'=> $grouphistory->getDescriptionChanged() ? 1 : -1,
				'url_changed'=> $grouphistory->getUrlChanged() ? 1 : -1,
				'twitter_username_changed'=> $grouphistory->getTwitterUsernameChanged() ? 1 : -1,
				'is_deleted_changed'=> $grouphistory->getIsDeletedChanged() ? 1 : -1,
			));
	}
	
}


