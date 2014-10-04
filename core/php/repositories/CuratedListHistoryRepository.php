<?php

namespace repositories;

use models\CuratedListModel;
use models\CuratedListHistoryModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListHistoryRepository {

	public function ensureChangedFlagsAreSet(CuratedListHistoryModel $curatedlisthistory) {
		global $DB;
		
		// do we already have them?
		if (!$curatedlisthistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM curated_list_history WHERE curated_list_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC");
		$stat->execute(array('id'=>$curatedlisthistory->getId(),'at'=>$curatedlisthistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$curatedlisthistory->setChangedFlagsFromNothing();
		} else {
			while($curatedlisthistory->isAnyChangeFlagsUnknown() && $lastHistoryData = $stat->fetch()) {
				$lastHistory = new CuratedListHistoryModel();
				$lastHistory->setFromDataBaseRow($lastHistoryData);
				$curatedlisthistory->setChangedFlagsFromLast($lastHistory);
			}
		}


		// Save back to DB
		$sqlFields = array();
		$sqlParams = array(
			'id'=>$curatedlisthistory->getId(),
			'created_at'=>$curatedlisthistory->getCreatedAt()->format("Y-m-d H:i:s"),
			'is_new'=>$curatedlisthistory->getIsNew()?1:0,
		);

		if ($curatedlisthistory->getTitleChangedKnown()) {
			$sqlFields[] = " title_changed = :title_changed ";
			$sqlParams['title_changed'] = $curatedlisthistory->getTitleChanged() ? 1 : -1;
		}
		if ($curatedlisthistory->getDescriptionChangedKnown()) {
			$sqlFields[] = " description_changed = :description_changed ";
			$sqlParams['description_changed'] = $curatedlisthistory->getDescriptionChanged() ? 1 : -1;
		}
		if ($curatedlisthistory->getIsDeletedChangedKnown()) {
			$sqlFields[] = " is_deleted_changed = :is_deleted_changed ";
			$sqlParams['is_deleted_changed'] = $curatedlisthistory->getIsDeletedChanged() ? 1 : -1;
		}

		$statUpdate = $DB->prepare("UPDATE curated_list_history SET ".
			" is_new = :is_new, ".
			implode(" , ",$sqlFields).
			" WHERE curated_list_id = :id AND created_at = :created_at");
		$statUpdate->execute($sqlParams);

	}

	
	
}


