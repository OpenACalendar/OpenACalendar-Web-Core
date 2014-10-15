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

	protected $possibleFields = array('title','slug','description_text','footer_text','is_web_robots_allowed',
		'is_closed_by_sys_admin','closed_by_sys_admin_reason','is_listed_in_index','is_feature_importer',
		'is_feature_curated_list','is_feature_map','is_feature_virtual_events',
		'is_feature_physical_events','is_feature_group','prompt_emails_days_in_advance','is_feature_tag');

	/**
	 * @param SiteModel $site
	 * @param $fields
	 * @param UserAccountModel $user As opposed to other DBAccess classes, User can not be NULL.
	 * @throws Exception
	 * @throws \Exception
	 */
	public function update(SiteModel $site, $fields, UserAccountModel $user ) {
		$alreadyInTransaction = $this->db->inTransaction();



		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$site->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'slug') {
				$fieldsParams1['slug'] = substr($site->getSlug(),0,VARCHAR_COLUMN_LENGTH_USED);
				$fieldsSQL1[] = " slug_canonical=:slug_canonical ";
				$fieldsParams1['slug_canonical'] = substr(SiteModel::makeCanonicalSlug($site->getSlug()),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'description_text') {
				$fieldsParams1['description_text'] = $site->getDescriptionText();
			} else if ($field == 'footer_text') {
				$fieldsParams1['footer_text'] = $site->getFooterText();
			} else if ($field == 'is_web_robots_allowed') {
				$fieldsParams1['is_web_robots_allowed'] = $site->getIsWebRobotsAllowed() ? 1 : 0;
			} else if ($field == 'is_closed_by_sys_admin') {
				$fieldsParams1['is_closed_by_sys_admin'] = $site->getIsClosedBySysAdmin() ? 1 : 0;
			} else if ($field == 'closed_by_sys_admin_reason') {
				$fieldsParams1['closed_by_sys_admin_reason'] = $site->getClosedBySysAdminReason();
			} else if ($field == 'is_listed_in_index') {
				$fieldsParams1['is_listed_in_index'] = $site->getIsListedInIndex() ? 1 : 0;
			} else if ($field == 'is_feature_importer') {
				$fieldsParams1['is_feature_importer'] = $site->getIsFeatureImporter() ? 1 : 0;
			} else if ($field == 'is_feature_curated_list') {
				$fieldsParams1['is_feature_curated_list'] = $site->getIsFeatureCuratedList() ? 1 : 0;
			} else if ($field == 'is_feature_map') {
				$fieldsParams1['is_feature_map'] = $site->getIsFeatureMap() ? 1 : 0;
			} else if ($field == 'is_feature_virtual_events') {
				$fieldsParams1['is_feature_virtual_events'] = $site->getIsFeatureVirtualEvents() ? 1 : 0;
			} else if ($field == 'is_feature_physical_events') {
				$fieldsParams1['is_feature_physical_events'] = $site->getIsFeaturePhysicalEvents() ? 1 : 0;
			} else if ($field == 'is_feature_group') {
				$fieldsParams1['is_feature_group'] = $site->getIsFeatureGroup() ? 1 : 0;
			} else if ($field == 'prompt_emails_days_in_advance') {
				$fieldsParams1['prompt_emails_days_in_advance'] = $site->getPromptEmailsDaysInAdvance();
			} else if ($field == 'is_feature_tag') {
				$fieldsParams1['is_feature_tag'] = $site->getIsFeatureTag() ? 1 : 0;
			}
		}

		// Make History Data
		$fieldsSQL2 = array('site_id','user_account_id','created_at');
		$fieldsSQLParams2 = array(':site_id',':user_account_id',':created_at');
		$fieldsParams2 = array(
			'site_id'=>$site->getId(),
			'user_account_id'=>($user ? $user->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
		);
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields)) {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'slug') {
					$fieldsParams2['slug'] = substr($site->getSlug(),0,VARCHAR_COLUMN_LENGTH_USED);
					$fieldsSQL2[] = " slug_canonical  ";
					$fieldsSQLParams2[] = " :slug_canonical  ";
					$fieldsParams2['slug_canonical'] = substr(SiteModel::makeCanonicalSlug($site->getSlug()),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'description_text') {
					$fieldsParams2['description_text'] = $site->getDescriptionText();
				} else if ($field == 'footer_text') {
					$fieldsParams2['footer_text'] = $site->getFooterText();
				} else if ($field == 'is_web_robots_allowed') {
					$fieldsParams2['is_web_robots_allowed'] = $site->getIsWebRobotsAllowed() ? 1 : 0;
				} else if ($field == 'is_closed_by_sys_admin') {
					$fieldsParams2['is_closed_by_sys_admin'] = $site->getIsClosedBySysAdmin() ? 1 : 0;
				} else if ($field == 'closed_by_sys_admin_reason') {
					$fieldsParams2['closed_by_sys_admin_reason'] = $site->getClosedBySysAdminReason();
				} else if ($field == 'is_listed_in_index') {
					$fieldsParams2['is_listed_in_index'] = $site->getIsListedInIndex() ? 1 : 0;
				} else if ($field == 'is_feature_importer') {
					$fieldsParams2['is_feature_importer'] = $site->getIsFeatureImporter() ? 1 : 0;
				} else if ($field == 'is_feature_curated_list') {
					$fieldsParams2['is_feature_curated_list'] = $site->getIsFeatureCuratedList() ? 1 : 0;
				} else if ($field == 'is_feature_map') {
					$fieldsParams2['is_feature_map'] = $site->getIsFeatureMap() ? 1 : 0;
				} else if ($field == 'is_feature_virtual_events') {
					$fieldsParams2['is_feature_virtual_events'] = $site->getIsFeatureVirtualEvents() ? 1 : 0;
				} else if ($field == 'is_feature_physical_events') {
					$fieldsParams2['is_feature_physical_events'] = $site->getIsFeaturePhysicalEvents() ? 1 : 0;
				} else if ($field == 'is_feature_group') {
					$fieldsParams2['is_feature_group'] = $site->getIsFeatureGroup() ? 1 : 0;
				} else if ($field == 'prompt_emails_days_in_advance') {
					$fieldsParams2['prompt_emails_days_in_advance'] = $site->getPromptEmailsDaysInAdvance();
				} else if ($field == 'is_feature_tag') {
					$fieldsParams2['is_feature_tag'] = $site->getIsFeatureTag() ? 1 : 0;
				}
				$fieldsSQL2[] = " ".$field."_changed ";
				$fieldsSQLParams2[] = " 0 ";
			} else {
				$fieldsSQL2[] = " ".$field."_changed ";
				$fieldsSQLParams2[] = " -2 ";
			}
		}



		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			// Information SQL
			$stat = $this->db->prepare("UPDATE site_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO site_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
			$stat->execute($fieldsParams2);

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
