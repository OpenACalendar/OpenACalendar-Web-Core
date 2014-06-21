<?php

namespace repositories;

use models\TagModel;
use models\TagHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagHistoryRepository {

	
	public function ensureChangedFlagsAreSet(TagHistoryModel $tagHistory) {
		global $DB;
		
		// do we already have them?
		if (!$tagHistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM tag_history WHERE tag_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$tagHistory->getId(),'at'=>$tagHistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$tagHistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new TagHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$tagHistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE tag_history SET ".
				" is_new = :is_new, ".
				" title_changed = :title_changed,  ".
				" description_changed = :description_changed,  ".
				" is_deleted_changed = :is_deleted_changed  ".
				"WHERE tag_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$tagHistory->getId(),
				'created_at'=>$tagHistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'is_new'=>$tagHistory->getIsNew()?1:0,
				'title_changed'=> $tagHistory->getTitleChanged() ? 1 : -1,
				'description_changed'=> $tagHistory->getDescriptionChanged() ? 1 : -1,
				'is_deleted_changed'=> $tagHistory->getIsDeletedChanged() ? 1 : -1,
			));
	}
	
}


