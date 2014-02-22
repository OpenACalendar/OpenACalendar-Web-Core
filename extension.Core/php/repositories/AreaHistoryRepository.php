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

	
	public function ensureChangedFlagsAreSet(AreaHistoryModel $areahistory) {
		global $DB;
		
		// do we already have them?
		if (!$areahistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM area_history WHERE area_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$areahistory->getId(),'at'=>$areahistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$areahistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new AreaHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$areahistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE area_history SET ".
				" title_changed = :title_changed,  ".
				" description_changed = :description_changed,  ".
				" country_id_changed = :country_id_changed,  ".
				" parent_area_id_changed = :parent_area_id_changed,  ".
				" is_deleted_changed = :is_deleted_changed  ".
				"WHERE area_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$areahistory->getId(),
				'created_at'=>$areahistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'title_changed'=> $areahistory->getTitleChanged() ? 1 : -1,
				'description_changed'=> $areahistory->getDescriptionChanged() ? 1 : -1,
				'country_id_changed'=> $areahistory->getCountryIdChanged() ? 1 : -1,
				'parent_area_id_changed'=> $areahistory->getParentAreaIdChanged() ? 1 : -1,
				'is_deleted_changed'=> $areahistory->getIsDeletedChanged() ? 1 : -1,
			));
	}
	
}


