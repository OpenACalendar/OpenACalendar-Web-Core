<?php

namespace repositories;

use models\SiteModel;
use models\SiteHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteHistoryRepository {

	
	public function ensureChangedFlagsAreSet(SiteHistoryModel $sitehistory) {
		global $DB;
		
		// do we already have them?
		if (!$sitehistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM site_history WHERE site_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC");
		$stat->execute(array('id'=>$sitehistory->getId(),'at'=>$sitehistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$sitehistory->setChangedFlagsFromNothing();
		} else {
			while($sitehistory->isAnyChangeFlagsUnknown() && $lastHistoryData = $stat->fetch()) {
				$lastHistory = new SiteHistoryModel();
				$lastHistory->setFromDataBaseRow($lastHistoryData);
				$sitehistory->setChangedFlagsFromLast($lastHistory);
			}
		}




		// Save back to DB
		$sqlFields = array();
		$sqlParams = array(
			'id'=>$sitehistory->getId(),
			'created_at'=>$sitehistory->getCreatedAt()->format("Y-m-d H:i:s"),
			'is_new'=>$sitehistory->getIsNew()?1:0,
		);


		if ($sitehistory->getTitleChangedKnown()) {
			$sqlFields[] = " title_changed = :title_changed ";
			$sqlParams['title_changed'] = $sitehistory->getTitleChanged() ? 1 : -1;
		}
		if ($sitehistory->getSlugChangedKnown()) {
			$sqlFields[] = " slug_changed = :slug_changed ";
			$sqlParams['slug_changed'] = $sitehistory->getSlugChanged() ? 1 : -1;
		}
		if ($sitehistory->getDescriptionTextChangedKnown()) {
			$sqlFields[] = " description_text_changed = :description_text_changed ";
			$sqlParams['description_text_changed'] = $sitehistory->getDescriptionTextChanged() ? 1 : -1;
		}
		if ($sitehistory->getFooterTextChangedKnown()) {
			$sqlFields[] = " footer_text_changed = :footer_text_changed ";
			$sqlParams['footer_text_changed'] = $sitehistory->getFooterTextChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsWebRobotsAllowedChangedKnown()) {
			$sqlFields[] = " is_web_robots_allowed_changed = :is_web_robots_allowed_changed ";
			$sqlParams['is_web_robots_allowed_changed'] = $sitehistory->getIsWebRobotsAllowedChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsClosedBySysAdminChangedKnown()) {
			$sqlFields[] = " is_closed_by_sys_admin_changed = :is_closed_by_sys_admin_changed ";
			$sqlParams['is_closed_by_sys_admin_changed'] = $sitehistory->getIsClosedBySysAdminChanged() ? 1 : -1;
		}
		if ($sitehistory->getClosedBySyAdminReasonChangedKnown()) {
			$sqlFields[] = " closed_by_sys_admin_reason_changed = :closed_by_sys_admin_reason_changed ";
			$sqlParams['closed_by_sys_admin_reason_changed'] = $sitehistory->getClosedBySyAdminReasonChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsListedInIndexChangedKnown()) {
			$sqlFields[] = " is_listed_in_index_changed = :is_listed_in_index_changed ";
			$sqlParams['is_listed_in_index_changed'] = $sitehistory->getIsListedInIndexChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeatureImporterChangedKnown()) {
			$sqlFields[] = " is_feature_importer_changed = :is_feature_importer_changed ";
			$sqlParams['is_feature_importer_changed'] = $sitehistory->getIsFeatureImporterChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeatureCuratedListChangedKnown()) {
			$sqlFields[] = " is_feature_curated_list_changed = :is_feature_curated_list_changed ";
			$sqlParams['is_feature_curated_list_changed'] = $sitehistory->getIsFeatureCuratedListChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeatureMapChangedKnown()) {
			$sqlFields[] = " is_feature_map_changed = :is_feature_map_changed ";
			$sqlParams['is_feature_map_changed'] = $sitehistory->getIsFeatureMapChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeatureVirtualEventsChangedKnown()) {
			$sqlFields[] = " is_feature_virtual_events_changed = :is_feature_virtual_events_changed ";
			$sqlParams['is_feature_virtual_events_changed'] = $sitehistory->getIsFeatureVirtualEventsChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeaturePhysicalEventsChangedKnown()) {
			$sqlFields[] = " is_feature_physical_events_changed = :is_feature_physical_events_changed ";
			$sqlParams['is_feature_physical_events_changed'] = $sitehistory->getIsFeaturePhysicalEventsChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeatureGroupChangedKnown()) {
			$sqlFields[] = " is_feature_group_changed = :is_feature_group_changed ";
			$sqlParams['is_feature_group_changed'] = $sitehistory->getIsFeatureGroupChanged() ? 1 : -1;
		}
		if ($sitehistory->getPromptEmailsDaysInAdvanceChangedKnown()) {
			$sqlFields[] = " prompt_emails_days_in_advance_changed = :prompt_emails_days_in_advance_changed ";
			$sqlParams['prompt_emails_days_in_advance_changed'] = $sitehistory->getPromptEmailsDaysInAdvanceChanged() ? 1 : -1;
		}
		if ($sitehistory->getIsFeatureTagChangedKnown()) {
			$sqlFields[] = " is_feature_tag_changed = :is_feature_tag_changed ";
			$sqlParams['is_feature_tag_changed'] = $sitehistory->getIsFeatureTagChanged() ? 1 : -1;
		}

		$statUpdate = $DB->prepare("UPDATE site_history SET ".
			" is_new = :is_new, ".
			implode(" , ",$sqlFields).
			" WHERE site_id = :id AND created_at = :created_at");
		$statUpdate->execute($sqlParams);
	}
	
}


