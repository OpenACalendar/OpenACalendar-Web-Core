<?php

namespace repositories;

use models\AreaModel;
use models\AreaHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaHistoryRepository {

	
	public function ensureChangedFlagsAreSet(AreaHistoryModel $areaHistory) {
		global $DB;
		
		// do we already have them?
		if (!$areaHistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM area_history WHERE area_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$areaHistory->getId(),'at'=>$areaHistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$areaHistory->setChangedFlagsFromNothing();
		} else {
			while($areaHistory->isAnyChangeFlagsUnknown() && $lastHistoryData = $stat->fetch()) {
				$lastHistory = new AreaHistoryModel();
				$lastHistory->setFromDataBaseRow($lastHistoryData);
				$areaHistory->setChangedFlagsFromLast($lastHistory);
			}
		}

		// Save back to DB
		$sqlFields = array();
		$sqlParams = array(
			'id'=>$areaHistory->getId(),
			'created_at'=>$areaHistory->getCreatedAt()->format("Y-m-d H:i:s"),
			'is_new'=>$areaHistory->getIsNew()?1:0,
		);

		if ($areaHistory->getTitleChangedKnown()) {
			$sqlFields[] = " title_changed = :title_changed ";
			$sqlParams['title_changed'] = $areaHistory->getTitleChanged() ? 1 : -1;
		}
		if ($areaHistory->getDescriptionChangedKnown()) {
			$sqlFields[] = " description_changed = :description_changed ";
			$sqlParams['description_changed'] = $areaHistory->getDescriptionChanged() ? 1 : -1;
		}
		if ($areaHistory->getCountryIdChangedKnown()) {
			$sqlFields[] = " country_id_changed = :country_id_changed ";
			$sqlParams['country_id_changed'] = $areaHistory->getCountryIdChanged() ? 1 : -1;
		}
		if ($areaHistory->getParentAreaIdChangedKnown()) {
			$sqlFields[] = " parent_area_id_changed = :parent_area_id_changed ";
			$sqlParams['parent_area_id_changed'] = $areaHistory->getParentAreaIdChanged() ? 1 : -1;
		}
		if ($areaHistory->getIsDuplicateOfIdChangedKnown()) {
			$sqlFields[] = " is_duplicate_of_id_changed  = :is_duplicate_of_id_changed ";
			$sqlParams['is_duplicate_of_id_changed'] = $areaHistory->getIsDuplicateOfIdChangedKnown() ? 1 : -1;
		}
		if ($areaHistory->getIsDeletedChangedKnown()) {
			$sqlFields[] = " is_deleted_changed = :is_deleted_changed ";
			$sqlParams['is_deleted_changed'] = $areaHistory->getIsDeletedChanged() ? 1 : -1;
		}

		$statUpdate = $DB->prepare("UPDATE area_history SET ".
			" is_new = :is_new, ".
			implode(" , ",$sqlFields).
			" WHERE area_id = :id AND created_at = :created_at");
		$statUpdate->execute($sqlParams);
	}
	
}


