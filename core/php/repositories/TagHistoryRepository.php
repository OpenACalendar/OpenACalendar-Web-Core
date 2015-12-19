<?php

namespace repositories;

use models\TagModel;
use models\TagHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagHistoryRepository {


	public function ensureChangedFlagsAreSet(TagHistoryModel $tagHistory) {
		global $DB;

		// do we already have them?
		if (!$tagHistory->isAnyChangeFlagsUnknown()) return;

		// load last.
		$stat = $DB->prepare("SELECT * FROM tag_history WHERE tag_id = :id AND created_at < :at ".
			"ORDER BY created_at DESC");
		$stat->execute(array('id'=>$tagHistory->getId(),'at'=>$tagHistory->getCreatedAt()->format("Y-m-d H:i:s")));


		// Apply what we know
		if ($stat->rowCount() == 0) {
			$tagHistory->setChangedFlagsFromNothing();
		} else {
			while($tagHistory->isAnyChangeFlagsUnknown() && $lastHistoryData = $stat->fetch()) {
				$lastHistory = new TagHistoryModel();
				$lastHistory->setFromDataBaseRow($lastHistoryData);
				$tagHistory->setChangedFlagsFromLast($lastHistory);
			}
		}

		// Save back to DB
		$sqlFields = array();
		$sqlParams = array(
			'id'=>$tagHistory->getId(),
			'created_at'=>$tagHistory->getCreatedAt()->format("Y-m-d H:i:s"),
			'is_new'=>$tagHistory->getIsNew()?1:0,
		);

		if ($tagHistory->getTitleChangedKnown()) {
			$sqlFields[] = " title_changed = :title_changed ";
			$sqlParams['title_changed'] = $tagHistory->getTitleChanged() ? 1 : -1;
		}
		if ($tagHistory->getDescriptionChangedKnown()) {
			$sqlFields[] = " description_changed = :description_changed ";
			$sqlParams['description_changed'] = $tagHistory->getDescriptionChanged() ? 1 : -1;
		}
		if ($tagHistory->getIsDeletedChangedKnown()) {
			$sqlFields[] = " is_deleted_changed = :is_deleted_changed ";
			$sqlParams['is_deleted_changed'] = $tagHistory->getIsDeletedChanged() ? 1 : -1;
		}
		$statUpdate = $DB->prepare("UPDATE tag_history SET ".
			" is_new = :is_new, ".implode(" , ",$sqlFields).
			" WHERE tag_id = :id AND created_at = :created_at");
		$statUpdate->execute($sqlParams);
	}

}


