<?php

namespace repositories;

use models\VenueModel;
use models\VenueHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueHistoryRepository {

	
	public function ensureChangedFlagsAreSet(VenueHistoryModel $venuehistory) {
		global $DB;
		
		// do we already have them?
		if (!$venuehistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM venue_history WHERE venue_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$venuehistory->getId(),'at'=>$venuehistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$venuehistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new VenueHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$venuehistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE venue_history SET ".
				" title_changed = :title_changed,  ".
				" description_changed = :description_changed,  ".
				" lat_changed = :lat_changed,  ".
				" lng_changed = :lng_changed,  ".
				" country_id_changed = :country_id_changed,  ".
				" area_id_changed = :area_id_changed,  ".
				" is_deleted_changed = :is_deleted_changed  ".
				"WHERE venue_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$venuehistory->getId(),
				'created_at'=>$venuehistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'title_changed'=> $venuehistory->getTitleChanged() ? 1 : -1,
				'description_changed'=> $venuehistory->getDescriptionChanged() ? 1 : -1,
				'lat_changed'=> $venuehistory->getLatChanged() ? 1 : -1,
				'lng_changed'=> $venuehistory->getLngChanged() ? 1 : -1,
				'is_deleted_changed'=> $venuehistory->getIsDeletedChanged() ? 1 : -1,
				'country_id_changed'=> $venuehistory->getCountryIdChanged() ? 1 : -1,
				'area_id_changed'=> $venuehistory->getAreaIdChanged() ? 1 : -1,
			));
	}
	
}


