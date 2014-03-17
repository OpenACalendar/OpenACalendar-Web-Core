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
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$sitehistory->getId(),'at'=>$sitehistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$sitehistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new SiteHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$sitehistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE site_history SET ".
				" is_new = :is_new, ".
				" title_changed = :title_changed,  ".
				" slug_changed = :slug_changed,  ".
				" description_text_changed = :description_text_changed,  ".
				" footer_text_changed = :footer_text_changed,  ".
				" is_web_robots_allowed_changed = :is_web_robots_allowed_changed,  ".
				" is_closed_by_sys_admin_changed = :is_closed_by_sys_admin_changed,  ".
				" is_all_users_editors_changed = :is_all_users_editors_changed,  ".
				" closed_by_sys_admin_reason_changed = :closed_by_sys_admin_reason_changed,  ".
				" is_listed_in_index_changed = :is_listed_in_index_changed,  ".
				" is_request_access_allowed_changed = :is_request_access_allowed_changed,  ".
				" request_access_question_changed = :request_access_question_changed,  ".
				" is_feature_map_changed = :is_feature_map_changed,  ".
				" is_feature_importer_changed = :is_feature_importer_changed,  ".
				" is_feature_curated_list_changed = :is_feature_curated_list_changed,  ".
				" prompt_emails_days_in_advance_changed = :prompt_emails_days_in_advance_changed,  ".
				" is_feature_virtual_events_changed = :is_feature_virtual_events_changed,  ".
				" is_feature_physical_events_changed = :is_feature_physical_events_changed,  ".
				" is_feature_group_changed = :is_feature_group_changed  ".
				"WHERE site_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$sitehistory->getId(),
				'created_at'=>$sitehistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'is_new'=>$sitehistory->getIsNew()?1:0,
				'title_changed'=> $sitehistory->getTitleChanged() ? 1 : -1,
				'slug_changed'=> $sitehistory->getSlugChanged() ? 1 : -1,
				'description_text_changed'=> $sitehistory->getDescriptionTextChanged() ? 1 : -1,
				'footer_text_changed'=> $sitehistory->getFooterTextChanged() ? 1 : -1,
				'is_web_robots_allowed_changed'=> $sitehistory->getIsWebRobotsAllowedChanged() ? 1 : -1,
				'is_closed_by_sys_admin_changed'=> $sitehistory->getIsClosedBySysAdminChanged() ? 1 : -1,
				'is_all_users_editors_changed'=> $sitehistory->getIsAlUsersEditorsChanged() ? 1 : -1,
				'closed_by_sys_admin_reason_changed'=> $sitehistory->getClosedBySyAdminReasonChanged() ? 1 : -1,
				'is_listed_in_index_changed'=> $sitehistory->getIsListedInIndexChanged() ? 1 : -1,
				'is_request_access_allowed_changed'=> $sitehistory->getIsRequestAccesAllowedChanged() ? 1 : -1,
				'request_access_question_changed'=> $sitehistory->getRequestAccessQuestionChanged() ? 1 : -1,
				'is_feature_map_changed'=> $sitehistory->getIsFeatureMapChanged() ? 1 : -1,
				'is_feature_importer_changed'=> $sitehistory->getIsFeatureImporterChanged() ? 1 : -1,
				'is_feature_curated_list_changed'=> $sitehistory->getIsFeatureCuratedListChanged() ? 1 : -1,
				'prompt_emails_days_in_advance_changed'=> $sitehistory->getPromptEmailsDaysInAdvanceChanged() ? 1 : -1,
				'is_feature_virtual_events_changed'=> $sitehistory->getIsFeatureVirtualEventsChanged() ? 1 : -1,
				'is_feature_physical_events_changed'=> $sitehistory->getIsFeaturePhysicalEventsChanged() ? 1 : -1,
				'is_feature_group_changed'=> $sitehistory->getIsFeatureGroupChanged() ? 1 : -1,
			));
	}
	
}


