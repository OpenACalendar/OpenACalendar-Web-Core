<?php


namespace dbaccess;

use models\UserAccountModel;
use models\SiteModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class SiteDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;

	/** @var \UserAgent */
	protected $useragent;

	function __construct($db, $timesource, $useragent)
	{
		$this->db = $db;
		$this->timesource = $timesource;
		$this->useragent = $useragent;
	}

	/**
	 * @param SiteModel $site
	 * @param $fields
	 * @param UserAccountModel $user As opposed to other DBAccess classes, User can not be NULL.
	 * @throws Exception
	 * @throws \Exception
	 */
	public function update(SiteModel $site, $fields, UserAccountModel $user ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			$stat = $this->db->prepare("UPDATE site_information SET title=:title, slug=:slug, slug_canonical=:slug_canonical, description_text = :description_text, ".
				" footer_text= :footer_text, is_web_robots_allowed=:is_web_robots_allowed, is_all_users_editors=:is_all_users_editors, ".
				"  is_closed_by_sys_admin=:is_closed_by_sys_admin, closed_by_sys_admin_reason=:closed_by_sys_admin_reason, is_listed_in_index=:is_listed_in_index, ".
				" request_access_question=:request_access_question, is_request_access_allowed=:is_request_access_allowed , ".
				" is_feature_importer=:is_feature_importer,is_feature_curated_list=:is_feature_curated_list, is_feature_map=:is_feature_map, ".
				" is_feature_virtual_events=:is_feature_virtual_events, is_feature_physical_events=:is_feature_physical_events , is_feature_group=:is_feature_group, ".
				" prompt_emails_days_in_advance=:prompt_emails_days_in_advance, is_feature_tag=:is_feature_tag ".
				" WHERE id=:id");
			$stat->execute(array(
				'title'=>substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'slug'=> $site->getSlug(),
				'slug_canonical'=>SiteModel::makeCanonicalSlug($site->getSlug()),
				'description_text'=>$site->getDescriptionText(),
				'footer_text'=>$site->getFooterText(),
				'is_web_robots_allowed'=>$site->getIsWebRobotsAllowed() ? 1 : 0,
				'is_all_users_editors'=>$site->getIsAllUsersEditors() ? 1 : 0,
				'is_closed_by_sys_admin'=>$site->getIsClosedBySysAdmin() ? 1 : 0,
				'is_listed_in_index'=>$site->getIsListedInIndex() ? 1 : 0,
				'is_request_access_allowed'=>$site->getIsRequestAccessAllowed() ? 1 : 0,
				'closed_by_sys_admin_reason'=> $site->getClosedBySysAdminReason(),
				'request_access_question'=> $site->getRequestAccessQuestion(),
				'id'=>$site->getId(),
				'is_feature_curated_list'=>$site->getIsFeatureCuratedList() ? 1 : 0,
				'is_feature_importer'=>$site->getIsFeatureImporter() ? 1 : 0,
				'is_feature_map'=>$site->getIsFeatureMap() ? 1 : 0,
				'is_feature_tag'=>$site->getIsFeatureTag() ? 1 : 0,
				'is_feature_virtual_events'=>$site->getIsFeatureVirtualEvents() ? 1 : 0,
				'is_feature_physical_events'=>$site->getIsFeaturePhysicalEvents() ? 1 : 0,
				'is_feature_group'=>$site->getIsFeatureGroup() ? 1 : 0,
				'prompt_emails_days_in_advance'=>$site->getPromptEmailsDaysInAdvance(),
			));


			$stat = $this->db->prepare("INSERT INTO site_history (site_id, user_account_id, title, slug, slug_canonical, created_at, description_text, footer_text, ".
				" is_web_robots_allowed, is_closed_by_sys_admin, closed_by_sys_admin_reason, is_all_users_editors, is_listed_in_index,request_access_question,".
				" is_request_access_allowed,is_feature_map,is_feature_importer,is_feature_curated_list, is_feature_virtual_events, is_feature_physical_events, is_feature_group, ".
				" prompt_emails_days_in_advance, is_feature_tag ) ".
				" VALUES (:site_id, :user_account_id, :title, :slug, :slug_canonical,  :created_at, :description_text, :footer_text, ".
				" :is_web_robots_allowed, :is_closed_by_sys_admin, :closed_by_sys_admin_reason, :is_all_users_editors, :is_listed_in_index,:request_access_question, ".
				" :is_request_access_allowed,:is_feature_map,:is_feature_importer,:is_feature_curated_list, :is_feature_virtual_events, :is_feature_physical_events, :is_feature_group, ".
				" :prompt_emails_days_in_advance, :is_feature_tag)");
			$stat->execute(array(
				'site_id'=>$site->getId(),
				'user_account_id'=>$user->getId(),
				'title'=>substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'slug'=> $site->getSlug(),
				'slug_canonical'=>SiteModel::makeCanonicalSlug($site->getSlug()),
				'created_at'=>  $this->timesource->getFormattedForDataBase(),
				'description_text'=>$site->getDescriptionText(),
				'footer_text'=>$site->getFooterText(),
				'is_web_robots_allowed'=>$site->getIsWebRobotsAllowed() ? 1 : 0,
				'is_closed_by_sys_admin'=>$site->getIsClosedBySysAdmin() ? 1 : 0,
				'is_all_users_editors'=>$site->getIsAllUsersEditors() ? 1 : 0,
				'is_listed_in_index'=>$site->getIsListedInIndex() ? 1 : 0,
				'is_request_access_allowed'=>$site->getIsRequestAccessAllowed() ? 1 : 0,
				'closed_by_sys_admin_reason'=> $site->getClosedBySysAdminReason(),
				'request_access_question'=> $site->getRequestAccessQuestion(),
				'is_feature_curated_list'=>$site->getIsFeatureCuratedList() ? 1 : 0,
				'is_feature_importer'=>$site->getIsFeatureImporter() ? 1 : 0,
				'is_feature_map'=>$site->getIsFeatureMap() ? 1 : 0,
				'is_feature_tag'=>$site->getIsFeatureTag() ? 1 : 0,
				'is_feature_virtual_events'=>$site->getIsFeatureVirtualEvents() ? 1 : 0,
				'is_feature_physical_events'=>$site->getIsFeaturePhysicalEvents() ? 1 : 0,
				'is_feature_group'=>$site->getIsFeatureGroup() ? 1 : 0,
				'prompt_emails_days_in_advance'=>$site->getPromptEmailsDaysInAdvance(),
			));


			if (!$alreadyInTransaction) {
				$this->db->commit();
			}
		} catch (Exception $e) {
			if (!$alreadyInTransaction) {
				$this->db->rollBack();
			}
			throw $e;
		}

	}


} 
